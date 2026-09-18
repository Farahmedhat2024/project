<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_login();

// ---------------------------------------------------------
// لازم يكون اليوزر مسجل دخول
// ---------------------------------------------------------
$user_id = $_SESSION['user_id'];
$errors  = [];

// ---------------------------------------------------------
// هات property_id من الرابط
// ---------------------------------------------------------
$property_id = isset($_GET['property_id']) ? (int) $_GET['property_id'] : (int) ($_POST['property_id'] ?? 0);

if ($property_id <= 0) {
    die('لا يوجد عقار محدد.');
}

$stmt = $conn->prepare("SELECT id, title FROM properties WHERE id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$property) {
    die('العقار غير موجود.');
}

// ---------------------------------------------------------
// لما اليوزر يبعت الفورم
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rating  = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'من فضلك اختاري تقييم من 1 إلى 5.';
    }

    if (empty($errors)) {
        // ندخل التقييم الجديد
        $insertStmt = $conn->prepare("
            INSERT INTO reviews (user_id, property_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        $insertStmt->bind_param('iiis', $user_id, $property_id, $rating, $comment);

        if ($insertStmt->execute()) {
            $insertStmt->close();

            // نحدّث متوسط التقييم وعدد المراجعات في جدول properties
            $updateStmt = $conn->prepare("
                UPDATE properties
                SET
                    rating = (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE property_id = ?),
                    review_count = (SELECT COUNT(*) FROM reviews WHERE property_id = ?)
                WHERE id = ?
            ");
            $updateStmt->bind_param('iii', $property_id, $property_id, $property_id);
            $updateStmt->execute();
            $updateStmt->close();

            // نرجع لصفحة تفاصيل الشقة
            header('Location: ../properties/details.php?id=' . $property_id . '&reviewed=1');
            exit;
        } else {
            $errors[] = 'حدث خطأ أثناء إضافة التقييم، حاولي مرة أخرى.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة تقييم - <?= htmlspecialchars($property['title']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 40px auto; }
        .box { border: 1px solid #ddd; border-radius: 10px; padding: 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        textarea { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; min-height: 100px; font-family: inherit; }
        .stars { display: flex; gap: 8px; font-size: 30px; margin-top: 8px; direction: ltr; justify-content: flex-end; }
        .stars input { display: none; }
        .stars label { margin: 0; cursor: pointer; color: #ddd; font-weight: normal; }
        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label { color: #f59e0b; }
        button { margin-top: 20px; padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        .error { color: #b91c1c; background: #fee2e2; padding: 10px; border-radius: 6px; margin-top: 10px; }
    </style>
</head>
<body>

<h2>تقييم: <?= htmlspecialchars($property['title']) ?></h2>

<?php if (!empty($errors)): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="box">
    <form method="POST" action="">
        <input type="hidden" name="property_id" value="<?= $property_id ?>">

        <label>التقييم</label>
        <div class="stars">
            <input type="radio" name="rating" id="star5" value="5"><label for="star5">★</label>
            <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
            <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
            <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
            <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
        </div>

        <label>تعليقك (اختياري)</label>
        <textarea name="comment" placeholder="شاركي تجربتك مع هذا المكان..."></textarea>

        <button type="submit">إرسال التقييم</button>
    </form>
</div>

</body>
</html>