
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_login();

$user_id = $_SESSION['user_id'];

$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    die('لا يوجد حجز محدد.');
}

// ---------------------------------------------------------
// تأكد إن الحجز ده بتاع نفس اليوزر وإنه لسه upcoming
// (منمنعش إلغاء حجز اتلغى أو خلص فعلاً)
// ---------------------------------------------------------
$stmt = $conn->prepare("
    SELECT id, status FROM bookings
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    die('الحجز غير موجود أو لا تملكين صلاحية إلغائه.');
}

if ($booking['status'] !== 'upcoming') {
    die('لا يمكن إلغاء هذا الحجز لأنه ليس ضمن الحجوزات القادمة.');
}

// ---------------------------------------------------------
// نفّذ الإلغاء (تحديث status فقط، مش حذف الصف)
// ---------------------------------------------------------
$updateStmt = $conn->prepare("
    UPDATE bookings SET status = 'cancelled'
    WHERE id = ? AND user_id = ?
");
$updateStmt->bind_param('ii', $booking_id, $user_id);
$updateStmt->execute();
$updateStmt->close();

// ارجعي لصفحة حجوزاتي بعد الإلغاء
header('Location: my-bookings.php?cancelled=1');
exit;