<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'index.php'; 

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    
    $formData = [
        'first_name'       => $first_name,
        'last_name'        => $last_name,
        'email'            => $email,
        'password'         => $password,
        'confirm_password' => $confirm,
    ];
    $errors = validate_registration_form($conn, $formData);

    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; 

        $stmt = mysqli_prepare($conn, "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $hashed_password, $role);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = "حصل خطأ أثناء التسجيل، حاول تاني";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء حساب جديد</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card-shell">
        <div class="auth-card">

            
            <div class="auth-card-hero">
                <div class="brand">stayease</div>
                <div>
                    <h2 class="hero-heading">اعمل حسابك دلوقتي</h2>
                    <p class="hero-caption">انضم لينا واحجز أماكن إقامة وتجارب مميزة في أي مكان في العالم.</p>
                </div>
                <div class="tab-nav">
                    <div class="active-bar pos-signup"></div>
                    <a href="login.php">تسجيل الدخول</a>
                    <a href="register.php" class="active">حساب جديد</a>
                </div>
            </div>

            
            <div class="auth-card-body">
                <div class="auth-form-wrap">

                    <?php if ($success): ?>
                        <h1>تم إنشاء الحساب</h1>
                        <p class="form-success">
                            حسابك جاهز دلوقتي. <a href="login.php">سجّل دخولك من هنا</a>
                        </p>
                    <?php else: ?>

                        <h1>إنشاء حساب جديد</h1>
                        

                        <?php if (!empty($errors)): ?>
                            <ul class="form-errors">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <form method="POST" action="register.php" novalidate>
                            <div class="field-row">
                                <div class="field">
                                    <label for="first_name">الاسم الأول</label>
                                    <input type="text" id="first_name" name="first_name"
                                        value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
                                </div>
                                <div class="field">
                                    <label for="last_name">الاسم الأخير</label>
                                    <input type="text" id="last_name" name="last_name"
                                        value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
                                </div>
                            </div>

                            <div class="field">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="email" id="email" name="email"
                                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                            </div>

                            <div class="field">
                                <label for="password">الباسورد</label>
                                <input type="password" id="password" name="password">
                            </div>

                            <div class="field">
                                <label for="confirm_password">تأكيد الباسورد</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>

                            <button type="submit" class="btn-primary">إنشاء الحساب</button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</body>
</html>