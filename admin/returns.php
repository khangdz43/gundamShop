<?php
require_once '../includes/auth.php';
requireEmployee();

$basePath = '../';
$message = "";
$success = false;

// Handle request status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_id'])) {
    $returnId = (int)$_POST['return_id'];
    $newStatus = $_POST['status']; // 'approved' or 'rejected'
    $adminComment = trim($_POST['admin_comment'] ?? '');

    if (in_array($newStatus, ['approved', 'rejected'])) {
        $conn->begin_transaction();
        try {
            // Get return details and order items
            $stmt = $conn->prepare("SELECT r.*, o.id as order_db_id, o.user_id as order_user_id FROM order_returns r JOIN orders o ON r.order_id = o.id WHERE r.id = ?");
            $stmt->bind_param("i", $returnId);
            $stmt->execute();
            $returnReq = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$returnReq) {
                throw new Exception("Yêu cầu đổi trả không tồn tại.");
            }

            // Update return status
            $stmt = $conn->prepare("UPDATE order_returns SET status = ?, admin_comment = ? WHERE id = ?");
            $stmt->bind_param("ssi", $newStatus, $adminComment, $returnId);
            $stmt->execute();
            $stmt->close();

            // If approved, refund stock and update order status (e.g. cancelled/returned)
            if ($newStatus === 'approved') {
                // Get order items
                $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmt->bind_param("i", $returnReq['order_db_id']);
                $stmt->execute();
                $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                foreach ($items as $item) {
                    $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                    $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                    $stmt->execute();
                    $stmt->close();
                }

                // Update order status to 'cancelled' or custom label
                $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $stmt->bind_param("i", $returnReq['order_db_id']);
                $stmt->execute();
                $stmt->close();
            }

            // Send notification to the user
            $notifTitle = "Cập nhật yêu cầu đổi trả";
            $notifMsg = "Yêu cầu đổi trả cho đơn hàng #" . $returnReq['id'] . " đã được " . ($newStatus === 'approved' ? 'chấp nhận' : 'từ chối') . ". Phản hồi: " . $adminComment;
            
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $returnReq['order_user_id'], $notifTitle, $notifMsg);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $message = "Cập nhật yêu cầu đổi trả thành công!";
            $success = true;
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Lỗi xử lý: " . $e->getMessage();
        }
    }
}

// Fetch returns list
$sql = "SELECT r.*, o.order_code, o.total, u.username 
        FROM order_returns r 
        JOIN orders o ON r.order_id = o.id 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
$returns = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Quản lý đổi trả - Gundam Store';
include '../includes/header.php';
?>

<div class="container">
    <h1 class="page-title">QUẢN LÝ ĐỔI TRẢ</h1>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>" style="margin-bottom: 20px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card" style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Ngày yêu cầu</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($returns)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-gray)">Không có yêu cầu đổi trả</td></tr>
                <?php else: ?>
                    <?php foreach ($returns as $r): ?>
                    <tr>
                        <td>#<?php echo $r['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($r['order_code']); ?></strong></td>
                        <td>@<?php echo htmlspecialchars($r['username']); ?></td>
                        <td><?php echo htmlspecialchars($r['reason']); ?></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <span class="status-badge status-pending">Chờ xử lý</span>
                            <?php elseif ($r['status'] === 'approved'): ?>
                                <span class="status-badge status-delivered">Chấp nhận</span>
                            <?php else: ?>
                                <span class="status-badge status-cancelled">Từ chối</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-blue btn-sm" onclick='showProcessModal(<?php echo json_encode($r); ?>)'>Xử lý</button>
                            <?php else: ?>
                                <span style="color:var(--text-gray); font-size:0.9rem;">
                                    <?php echo htmlspecialchars($r['admin_comment'] ?: 'Không có phản hồi'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Process Modal -->
<div id="processModal" class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); display: none; justify-content: center; align-items: center; z-index: 1000;">
    <div class="card" style="width: 90%; max-width: 500px; border: 1px solid var(--primary-blue); box-shadow: 0 10px 30px rgba(31,95,255,0.2);">
        <h2 style="margin-top: 0; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;"><i class="fas fa-edit"></i> Xử lý đổi trả</h2>
        
        <form method="POST" action="">
            <input type="hidden" name="return_id" id="modalReturnId">
            
            <p><strong>Mã đơn hàng:</strong> <span id="modalOrderCode" style="color:var(--primary-blue); font-weight:bold;"></span></p>
            <p><strong>Khách hàng:</strong> <span id="modalUsername"></span></p>
            <p><strong>Lý do đổi trả:</strong></p>
            <blockquote id="modalReason" style="background: rgba(255,255,255,0.05); padding: 10px; border-left: 3px solid var(--primary-blue); border-radius: 4px; font-style: italic; margin-bottom: 20px;"></blockquote>
            
            <div class="form-group">
                <label>Quyết định</label>
                <select name="status" class="form-control" required>
                    <option value="approved">Chấp nhận đổi trả (Hoàn kho & Hủy đơn)</option>
                    <option value="rejected">Từ chối đổi trả</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Phản hồi cho khách hàng</label>
                <textarea name="admin_comment" class="form-control" rows="3" placeholder="Nhập phản hồi hoặc lý do từ chối..."></textarea>
            </div>
            
            <div style="display:flex; gap:12px; margin-top:20px; justify-content:flex-end;">
                <button type="button" class="btn btn-gray" onclick="closeProcessModal()">Hủy</button>
                <button type="submit" class="btn btn-blue">Xác nhận</button>
            </div>
        </form>
    </div>
</div>

<script>
function showProcessModal(req) {
    document.getElementById('modalReturnId').value = req.id;
    document.getElementById('modalOrderCode').textContent = req.order_code;
    document.getElementById('modalUsername').textContent = '@' + req.username;
    document.getElementById('modalReason').textContent = req.reason;
    document.getElementById('processModal').style.display = 'flex';
}

function closeProcessModal() {
    document.getElementById('processModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('processModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProcessModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
