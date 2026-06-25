<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$message = '';
$message_type = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');

    if (strlen($username) < 3) {
        $message = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
    } elseif (!preg_match('/^[a-zA-Z0-9_\.]+$/', $username)) {
        $message = 'Tên đăng nhập chỉ nên dùng chữ, số, dấu gạch dưới hoặc dấu chấm.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ.';
    } elseif (strlen($password) < 7) {
        $message = 'Mật khẩu phải có ít nhất 7 ký tự.';
    } elseif ($password === $username) {
        $message = 'Mật khẩu không được trùng với tên đăng nhập.';
    } elseif ($password !== $confirm_password) {
        $message = 'Mật khẩu nhập lại không trùng khớp.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $message = 'Tên đăng nhập hoặc email đã tồn tại.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hash, $email, $fullName);
            if ($stmt->execute()) {
                $_SESSION['flash']['login'] = [
                    'message' => 'Đăng ký thành công. Bạn có thể đăng nhập ngay.',
                    'type' => 'success'
                ];
                $stmt->close();
                redirect('login.php');
            }

            $message = 'Đăng ký thất bại, vui lòng thử lại.';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Gundam Store</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <i class="fas fa-user-astronaut"></i>
            <span>Gundam Store HUMG</span>
        </div>
        <h1>Tạo tài khoản</h1>
        <p class="auth-subtitle">Lập tài khoản để mua hàng nhanh hơn, lưu lịch sử chat AI và theo dõi đơn hàng dễ dàng.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Tối thiểu 3 ký tự" required minlength="3"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Tên để shop gọi bạn"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="ban@example.com" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Tối thiểu 7 ký tự" required minlength="7" style="padding-right: 40px;">
                    <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; z-index: 10;"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Nhập lại mật khẩu</label>
                <div style="position: relative;">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required minlength="7" style="padding-right: 40px;">
                    <i class="fas fa-eye" id="toggleConfirmPassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; z-index: 10;"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-blue" style="width:100%">Tạo tài khoản</button>
        </form>

        <div class="auth-link">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
            <p style="margin-top:10px"><a href="index.php"><i class="fas fa-arrow-left"></i> Về trang chủ</a></p>
        </div>
    </div>
</div>

<script>
function setupPasswordToggle(inputId, toggleId) {
    const toggleElement = document.getElementById(toggleId);
    if (toggleElement) {
        toggleElement.addEventListener('click', function () {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    }
}
setupPasswordToggle('password', 'togglePassword');
setupPasswordToggle('confirm_password', 'toggleConfirmPassword');
</script>
</body>
</html>
