<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser($conn);
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';

    if (!empty($newPass)) {
        if (strlen($newPass) < 7) {
            $message = __('err_password_short');
            $messageType = 'error';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!password_verify($currentPass, $row['password'])) {
                $message = __('err_current_password');
                $messageType = 'error';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $fullName, $phone, $address, $hash, $user['id']);
                $stmt->execute();
                $stmt->close();
                $message = __('profile_password_updated');
            }
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullName, $phone, $address, $user['id']);
        $stmt->execute();
        $stmt->close();
        $message = __('profile_updated');
    }
    $user = getCurrentUser($conn);
}

$pageTitle = __('profile_title') . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="container" style="max-width:600px">
    <h1 class="page-title"><?php echo __('profile_title'); ?></h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label><?php echo __('username'); ?></label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            <div class="form-group">
                <label><?php echo __('email'); ?></label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label><?php echo __('full_name'); ?></label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo __('phone'); ?></label>
                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo __('address'); ?></label>
                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            <hr style="border-color:var(--border-color);margin:24px 0">
            <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text-gray)"><?php echo __('change_password'); ?></h3>
            <div class="form-group">
                <label><?php echo __('current_password'); ?></label>
                <input type="password" name="current_password" class="form-control">
            </div>
            <div class="form-group">
                <label><?php echo __('new_password'); ?></label>
                <input type="password" name="new_password" class="form-control" minlength="7">
            </div>
            <button type="submit" class="btn btn-blue"><i class="fas fa-save"></i> <?php echo __('save_changes'); ?></button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
