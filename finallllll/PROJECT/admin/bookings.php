<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_admin();

// ---------------------------------------------------------
// تغيير حالة حجز
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $bookingId = (int) $_POST['booking_id'];
    $newStatus = $_POST['status'];
    $allowed = ['upcoming', 'completed', 'cancelled'];

    if (in_array($newStatus, $allowed)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $newStatus, $bookingId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: bookings.php?updated=1');
    exit;
}

// ---------------------------------------------------------
// هات كل الحجوزات مع اسم اليوزر والشقة
// ---------------------------------------------------------
$bookings = $conn->query("
    SELECT
        b.id, b.check_in, b.check_out, b.guests, b.total_price, b.status,
        u.first_name, u.last_name,
        p.title AS property_title
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN properties p ON b.property_id = p.id
    ORDER BY b.id DESC
");

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
    <title>إدارة الحجوزات - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .navbar { background: #111827; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #d1d5db; text-decoration: none; margin-right: 20px; }
        .navbar a:hover { color: #fff; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-size: 14px; }
        th { background: #f9fafb; }
        select { padding: 5px; border-radius: 6px; border: 1px solid #ddd; }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; }
        .badge.upcoming { background: #dbeafe; color: #1e40af; }
        .badge.completed { background: #dcfce7; color: #166534; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .msg { padding: 10px; border-radius: 6px; margin-bottom: 15px; background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

<nav class="navbar">
    <div><strong>لوحة تحكم StayEase</strong></div>
    <div>
        <a href="dashboard.php">الرئيسية</a>
        <a href="properties.php">الشقق</a>
        <a href="users.php">المستخدمين</a>
        <a href="bookings.php">الحجوزات</a>
        <a href="reviews.php">التقييمات</a>
    </div>
</nav>

<div class="container">
    <h1>إدارة الحجوزات</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="msg">تم تحديث الحالة بنجاح.</div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>المستخدم</th>
                <th>الشقة</th>
                <th>التواريخ</th>
                <th>الإجمالي</th>
                <th>الحالة</th>
                <th>تغيير الحالة</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($b = $bookings->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></td>
                    <td><?= htmlspecialchars($b['property_title']) ?></td>
                    <td><?= htmlspecialchars($b['check_in']) ?> → <?= htmlspecialchars($b['check_out']) ?></td>
                    <td><?= number_format($b['total_price'], 2) ?> EGP</td>
                    <td>
                        <span class="badge <?= $b['status'] ?>">
                            <?= $statusLabels[$b['status']] ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="" style="display:flex; gap:6px;">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <select name="status">
                                <?php foreach ($statusLabels as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $b['status'] === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">حفظ</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>