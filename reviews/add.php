`php
<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$errors = [];

$booking_id = isset($_GET['booking_id'])
    ? (int) $_GET['booking_id']
    : (int) ($_POST['booking_id'] ?? 0);

if ($booking_id <= 0) {
    die('لا يوجد حجز محدد.');
}

// جلب بيانات الحجز والعقار
$stmt = $conn->prepare("
    SELECT
        b.id AS booking_id,
        b.property_id,
        b.user_id,
        b.status,
        b.check_out,
        p.title
    FROM bookings b
    INNER JOIN properties p ON p.id = b.property_id
    WHERE b.id = ?
      AND b.user_id = ?
");

$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();

$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    die('الحجز غير موجود أو لا تملكين صلاحية تقييمه.');
}

$property_id = (int) $booking['property_id'];

// لازم الحجز يكون مكتمل
if ($booking['status'] !== 'completed') {
    die('لا يمكن تقييم الحجز إلا بعد انتهاء الإقامة.');
}

// التأكد أن الحجز لم يتم تقييمه من قبل
$checkStmt = $conn->prepare("
    SELECT id
    FROM reviews
    WHERE booking_id = ?
");

$checkStmt->bind_param('i', $booking_id);
$checkStmt->execute();

$existingReview = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($existingReview) {
    die('تم تقييم هذا الحجز من قبل.');
}

// عند إرسال الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'من فضلك اختاري تقييم من 1 إلى 5.';
    }

    if (empty($errors)) {

        $insertStmt = $conn->prepare("
            INSERT INTO reviews
            (user_id, property_id, booking_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
        ");

        $insertStmt->bind_param(
            'iiiis',
            $user_id,
            $property_id,
            $booking_id,
            $rating,
            $comment
        );

        if ($insertStmt->execute()) {

            $insertStmt->close();

            // تحديث متوسط التقييم وعدد التقييمات
            $updateStmt = $conn->prepare("
                UPDATE properties
                SET
                    rating = (
                        SELECT ROUND(AVG(rating), 1)
                        FROM reviews
                        WHERE property_id = ?
                    ),
                    review_count = (
                        SELECT COUNT(*)
                        FROM reviews
                        WHERE property_id = ?
                    )
                WHERE id = ?
            ");

            $updateStmt->bind_param(
                'iii',
                $property_id,
                $property_id,
                $property_id
            );

            $updateStmt->execute();
            $updateStmt->close();

            header(
                'Location: ../properties/details.php?id='
                . $property_id
                . '&reviewed=1'
            );
            exit;

        } else {

            $insertStmt->close();

            $errors[] = 'حدث خطأ أثناء إضافة التقييم.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <title>
        إضافة تقييم - <?= htmlspecialchars($booking['title']) ?>
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
        }

        .box {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
            min-height: 100px;
            font-family: inherit;
        }
        .stars {
            display: flex;
            gap: 8px;
            font-size: 30px;
            margin-top: 8px;
            direction: ltr;
        }

        .stars input {
            display: none;
        }

        .stars label {
            margin: 0;
            cursor: pointer;
            color: #ddd;
            font-weight: normal;
        }

        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label {
            color: #f59e0b;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            color: #b91c1c;
            background: #fee2e2;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
        }

    </style>

</head>

<body>

<h2>
    تقييم: <?= htmlspecialchars($booking['title']) ?>
</h2>

<?php if (!empty($errors)): ?>

    <div class="error">

        <ul>

            <?php foreach ($errors as $err): ?>

                <li>
                    <?= htmlspecialchars($err) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>

<div class="box">

    <form method="POST">

        <input
            type="hidden"
            name="booking_id"
            value="<?= $booking_id ?>"
        >

        <label>التقييم</label>

        <div class="stars">

            <input type="radio" name="rating" id="star5" value="5">
            <label for="star5">★</label>

            <input type="radio" name="rating" id="star4" value="4">
            <label for="star4">★</label>

            <input type="radio" name="rating" id="star3" value="3">
            <label for="star3">★</label>

            <input type="radio" name="rating" id="star2" value="2">
            <label for="star2">★</label>

            <input type="radio" name="rating" id="star1" value="1">
            <label for="star1">★</label>

        </div>

        <label>تعليقك (اختياري)</label>

        <textarea
            name="comment"
            placeholder="شاركي تجربتك مع هذا المكان..."
        ></textarea>

        <button type="submit">
            إرسال التقييم
        </button>

    </form>

</div>

</body>

</html>
`