<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/config.php';   

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "من فضلك املأ الإيميل والباسورد";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صحيح";
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id']    = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role']       = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $errors[] = "الإيميل أو الباسورد غلط";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card-shell">
        <div class="auth-card">


        <div class="auth-card-hero">
                <div class="brand">stayease</div>
                <div>
                    <h2 class="hero-heading">أهلاً بيك تاني</h2>
                    <p class="hero-caption">سجّل دخولك عشان تكمل رحلتك وتشوف حجوزاتك.</p>
                </div>
                <div class="tab-nav">
                    <div class="active-bar pos-signin"></div>
                    <a href="login.php" class="active">تسجيل الدخول</a>
                    <a href="register.php">حساب جديد</a>
                </div>
            </div>


            <div class="auth-card-body">
                <div class="auth-form-wrap">

                    <h1>تسجيل الدخول</h1>
                    <p class="sub">أدخل بياناتك للمتابعة</p>

                    <?php if (!empty($errors)): ?>
                        <ul class="form-errors">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <form method="POST" action="login.php" novalidate>
                        <div class="field">
                            <label for="email">البريد الإلكتروني</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        </div>

                        <div class="field">
                            <label for="password">الباسورد</label>
                            <input type="password" id="password" name="password">
                        </div>

                        <button type="submit" class="btn-primary">دخول</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</body>
</html>