<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/index.php' : 'index.php');
}

$message = '';
$message_type = 'error';
$savedUsername = getRememberedUsername();

if (!empty($_SESSION['flash']['login'])) {
    $message = $_SESSION['flash']['login']['message'];
    $message_type = $_SESSION['flash']['login']['type'];
    unset($_SESSION['flash']['login']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if ($username === '' || $password === '') {
        $message = __('err_login_required');
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role, is_active FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
            loginUser($user);

            if ($remember) {
                setRememberMe($conn, $user['id'], $user['username']);
            } else {
                clearRememberMe($conn, $user['id']);
            }

            $redirect = $_SESSION['redirect_after_login'] ?? (isAdmin() || isEmployee() ? 'admin/index.php' : 'index.php');
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            $message = __('err_login_invalid');
        }
    }
    $savedUsername = $username;
}
?>
<!DOCTYPE html>
<html lang="<?php echo currentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login_title'); ?> - Gundam Store</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div style="display:flex;justify-content:flex-end;gap:4px;margin-bottom:10px;">
            <a href="<?php echo htmlspecialchars(langUrl('vi')); ?>" class="btn-header btn-sm <?php echo currentLang() === 'vi' ? 'active-lang' : ''; ?>" style="padding:4px 8px;font-size:0.75rem;font-weight:700;">VI</a>
            <a href="<?php echo htmlspecialchars(langUrl('en')); ?>" class="btn-header btn-sm <?php echo currentLang() === 'en' ? 'active-lang' : ''; ?>" style="padding:4px 8px;font-size:0.75rem;font-weight:700;">EN</a>
        </div>
        <div class="auth-brand">
            <img src="assets/images/LOGO.jpg" alt="Logo" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
            <span>Gundam Store HUMG</span>
        </div>
        <h1><?php echo __('login_title'); ?></h1>
        <p class="auth-subtitle"><?php echo __('login_subtitle'); ?></p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username"><?php echo __('login_username'); ?></label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Ví dụ: amuro_ray" required autofocus
                       value="<?php echo htmlspecialchars($savedUsername); ?>">
            </div>
            <div class="form-group">
                <label for="password"><?php echo __('login_password'); ?></label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" class="form-control" required style="padding-right: 40px;">
                    <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; z-index: 10;"></i>
                </div>
            </div>
            <div class="form-group remember-group">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember" value="1"
                        <?php echo !empty($_COOKIE[REMEMBER_COOKIE]) ? 'checked' : ''; ?>>
                    <span><?php echo __('remember_me'); ?></span>
                </label>
            </div>
            <button type="submit" class="btn btn-red" style="width:100%"><?php echo __('login_title'); ?></button>
        </form>

        <div class="auth-link">
            <p><?php echo __('no_account'); ?> <a href="register.php"><?php echo __('create_account'); ?></a></p>
            <p style="margin-top:10px"><a href="index.php"><i class="fas fa-arrow-left"></i> <?php echo __('back_home_link'); ?></a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    }
});
</script>
</body>
</html>
