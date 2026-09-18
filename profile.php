<?php
require_once 'includes/auth_guard.php';
require_login(); 
 
require_once 'config/config.php';
 
$user_id = $_SESSION['user_id'];
$errors  = [];
$success = false;
 

$stmt = mysqli_prepare($conn, "SELECT first_name, last_name, email, password FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
 
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
 
   
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    $_SESSION = [];
    session_destroy();
 
    header("Location: register.php");
    exit;
}
 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
 
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $errors[] = "من فضلك املأ كل الحقول";
    }
 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صحيح";
    }
 
    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($check, "si", $email, $user_id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $errors[] = "البريد الإلكتروني ده مستخدم بالفعل";
        }
        mysqli_stmt_close($check);
    }
 
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_new_password'] ?? '';
 
    if (!empty($new_password)) {
 
        
        if (empty($current_password)) {
            $errors[] = "لازم تكتب الباسورد الحالي عشان تقدر تغيّره";
        }
        
        elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "الباسورد الحالي غلط";
        }
 
      
        if (strlen($new_password) < 6) {
            $errors[] = "الباسورد الجديد لازم يكون 6 حروف على الأقل";
        }
 
        
        if ($new_password !== $confirm_password) {
            $errors[] = "الباسورد الجديد وتأكيده مش متطابقين";
        }
 
       
        if (!empty($current_password) && $new_password === $current_password) {
            $errors[] = "الباسورد الجديد لازم يكون مختلف عن القديم";
        }
    }
 
    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET first_name=?, last_name=?, email=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $first_name, $last_name, $email, $hashed, $user_id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $first_name, $last_name, $email, $user_id);
        }
 
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            $_SESSION['first_name'] = $first_name; 
            $user['first_name'] = $first_name;
            $user['last_name']  = $last_name;
            $user['email']      = $email;
            if (!empty($new_password)) {
                $user['password'] = $hashed; 
            }
        } else {
            $errors[] = "حصل خطأ أثناء التحديث، حاول تاني";
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
    <title>الملف الشخصي</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card-shell">
        <div class="auth-card">
 
            <div class="auth-card-hero">
                <div class="brand">Nestay</div>
                <div>
                    <h2 class="hero-heading">بياناتك الشخصية</h2>
                    <p class="hero-caption">عدّل بياناتك الأساسية أو غيّر الباسورد بتاعك.</p>
                </div>
                <div>
                    <a href="index.php" style="color:#C9D6D1;">للرئيسية</a> ·
                    <a href="logout.php" style="color:#C9D6D1;">تسجيل خروج</a>
                </div>
            </div>
 
            <div class="auth-card-body">
                <div class="auth-form-wrap">
 
                    <h1>تعديل الملف الشخصي</h1>
                    <p class="sub">أهلاً <?php echo htmlspecialchars($user['first_name']); ?></p>
 
                    <?php if ($success): ?>
                        <p class="form-success">تم حفظ التعديلات بنجاح</p>
                    <?php endif; ?>
 
                    <?php if (!empty($errors)): ?>
                        <ul class="form-errors">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
 
                    <form method="POST" action="profile.php" novalidate>
                        <div class="field-row">
                            <div class="field">
                                <label for="first_name">الاسم الأول</label>
                                <input type="text" id="first_name" name="first_name"
                                    value="<?php echo htmlspecialchars($user['first_name']); ?>">
                            </div>
                            <div class="field">
                                <label for="last_name">الاسم الأخير</label>
                                <input type="text" id="last_name" name="last_name"
                                    value="<?php echo htmlspecialchars($user['last_name']); ?>">
                            </div>
                        </div>
 
                        <div class="field">
                            <label for="email">البريد الإلكتروني</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
 
                        <hr style="margin:2rem 0 1.5rem; border:none; border-top:1px solid var(--line);">
                        <p class="sub" style="margin-bottom:1.2rem;">تغيير الباسورد </p>
 
                        <div class="field">
                            <label for="current_password">الباسورد الحالي</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
 
                        <div class="field">
                            <label for="new_password">الباسورد الجديد</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
 
                        <div class="field">
                            <label for="confirm_new_password">تأكيد الباسورد الجديد</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password">
                        </div>
 
                        <button type="submit" class="btn-primary">حفظ التعديلات</button>
                    </form>
 
                    <hr style="margin:2rem 0; border:none; border-top:1px solid var(--line);">
 
                    <form method="POST" action="profile.php"
                        onsubmit="return confirm('هل انت متأكد ');">
                        <input type="hidden" name="delete_account" value="1">
                        <button type="submit" style="width:100%; background:transparent; color:var(--error); border:1.5px solid var(--error); padding:0.85rem 1rem; border-radius:4px; font-family:inherit; font-size:1rem; cursor:pointer;">
                            حذف حسابي نهائيًا
                        </button>
                    </form>
 
                </div>
            </div>
 
        </div>
    </div>
</body>
</html>