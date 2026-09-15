<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/config.php';
require_once 'validate.php';



function apply_validation_rules($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';

        if (trim($value) === '') {
            $errors[] = "حقل \"$field\" مطلوب";
            continue;
        }

        $options = $rule['my_options'] ?? null;
        $result = $options ? filter_var($value, $rule['filters'], $options)
                            : filter_var($value, $rule['filters']);

        if ($result === false) {
            $errors[] = $rule['error'];
        }
    }

    return $errors;
}

function validate_password_match($password, $confirm) {
    if ($password !== $confirm) {
        return "الباسورد وتأكيد الباسورد مش متطابقين";
    }
    return null;
}

function email_exists($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function validate_registration_form($conn, $data) {
    global $validates;

    $errors = apply_validation_rules($data, $validates);

    if (!empty($errors)) {
        return $errors;
    }

    if ($err = validate_password_match($data['password'], $data['confirm_password'] ?? '')) {
        $errors[] = $err;
    }

    if (empty($errors) && email_exists($conn, $data['email'])) {
        $errors[] = "البريد الإلكتروني ده مسجل قبل كده";
    }

    return $errors;
}



if (basename($_SERVER['SCRIPT_FILENAME']) === 'index.php'):
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nestay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div style="padding:3rem; text-align:center;">
        <h1>Nestay</h1>
        <p>هربطها مع صفحة التانيه </p>
        <?php if (isset($_SESSION['user_id'])): ?>
<p>أهلاً <?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> - <a href="profile.php">الملف الشخصي</a> - <a href="logout.php">تسجيل خروج</a></p>        <?php else: ?>
            <p><a href="login.php">تسجيل الدخول</a> | <a href="register.php">إنشاء حساب</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php endif; ?>