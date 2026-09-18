<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_login();

$user_id = $_SESSION['user_id'];

// ---------------------------------------------------------
// هات booking_id من الرابط
// ---------------------------------------------------------
$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    die('لا يوجد حجز محدد.');
}

// ---------------------------------------------------------
// هات تفاصيل الحجز + بيانات الشقة (JOIN)
// نتأكد إن الحجز ده بتاع نفس اليوزر بس (أمان)
// ---------------------------------------------------------
$stmt = $conn->prepare("
    SELECT
        b.id, b.check_in, b.check_out, b.guests, b.price_per_night,
        b.nights, b.total_price, b.status, b.created_at,
        p.title AS property_title
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    die('الحجز غير موجود أو لا تملكين صلاحية عرضه.');
}

// ترجمة حالة الحجز للعربي
$statusLabels = [
    'upcoming'  => 'قادم',
    'completed' => 'مكتمل',
    'cancelled' => 'ملغى',
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تأكيد الحجز</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 40px auto; background: #f9fafb; }
        .card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .success-icon { text-align: center; font-size: 50px; }
        h2 { text-align: center; color: #16a34a; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 10px 0; border-bottom: 1px solid #eee; }
        td:first-child { color: #6b7280; }
        td:last-child { font-weight: bold; text-align: left; }
        .total { font-size: 20px; color: #2563eb; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; }
        .status.upcoming { background: #dbeafe; color: #1e40af; }
        .status.completed { background: #dcfce7; color: #166534; }
        .status.cancelled { background: #fee2e2; color: #991b1b; }
        .actions { text-align: center; margin-top: 25px; }
        .actions a { display: inline-block; margin: 0 8px; padding: 10px 20px; border-radius: 6px; text-decoration: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #111; }
    </style>
</head>
<body>

<div class="card">
    <div class="success-icon">✅</div>
    <h2>تم تأكيد الحجز بنجاح</h2>

    <table>
        <tr><td>رقم الحجز</td><td>#<?= $booking['id'] ?></td></tr>
        <tr><td>الشقة</td><td><?= htmlspecialchars($booking['property_title']) ?></td></tr>
        <tr><td>تاريخ الوصول</td><td><?= htmlspecialchars($booking['check_in']) ?></td></tr>
        <tr><td>تاريخ المغادرة</td><td><?= htmlspecialchars($booking['check_out']) ?></td></tr>
        <tr><td>عدد الليالي</td><td><?= $booking['nights'] ?></td></tr>
        <tr><td>عدد الأفراد</td><td><?= $booking['guests'] ?></td></tr>
        <tr><td>السعر لليلة</td><td><?= number_format($booking['price_per_night'], 2) ?> EGP</td></tr>
        <tr><td>الإجمالي</td><td class="total"><?= number_format($booking['total_price'], 2) ?> EGP</td></tr>
        <tr>
            <td>الحالة</td>
            <td>
                <span class="status <?= $booking['status'] ?>">
                    <?= $statusLabels[$booking['status']] ?? $booking['status'] ?>
                </span>
            </td>
        </tr>
    </table>

    <div class="actions">
        <a href="my-bookings.php" class="btn-primary">عرض كل حجوزاتي</a>
        <a href="../properties/index.php" class="btn-secondary">تصفح شقق أخرى</a>
    </div>
</div>

</body>
</html>