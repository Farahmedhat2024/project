<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_login();

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$review_id = isset($_GET['review_id']) ? (int) $_GET['review_id'] : 0;

if ($review_id <= 0) {
    die('لا يوجد تقييم محدد.');
}

// ---------------------------------------------------------
// هات التقييم، ونتأكد إنه بتاع نفس اليوزر أو إن اليوزر أدمن
// ---------------------------------------------------------
$stmt = $conn->prepare("SELECT id, user_id, property_id FROM reviews WHERE id = ?");
$stmt->bind_param('i', $review_id);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$review) {
    die('التقييم غير موجود.');
}

if (!$is_admin && $review['user_id'] != $user_id) {
    die('لا تملكين صلاحية حذف هذا التقييم.');
}

$property_id = $review['property_id'];

// ---------------------------------------------------------
// نفّذ الحذف
// ---------------------------------------------------------
$deleteStmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
$deleteStmt->bind_param('i', $review_id);
$deleteStmt->execute();
$deleteStmt->close();

// ---------------------------------------------------------
// نحدّث متوسط التقييم وعدد المراجعات بعد الحذف
// ---------------------------------------------------------
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

// ---------------------------------------------------------
// رجّعي المستخدم للمكان المناسب حسب نوعه
// ---------------------------------------------------------
if ($is_admin) {
    header('Location: ../admin/reviews.php?deleted=1');
} else {
    header('Location: ../properties/details.php?id=' . $property_id . '&deleted=1');
}
exit;