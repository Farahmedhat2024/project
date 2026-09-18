<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_login();

$user_id = $_SESSION['user_id'];

// ---------------------------------------------------------
// تحديث تلقائي: أي حجز upcoming وميعاده فات يبقى completed
// ---------------------------------------------------------
$conn->query("
    UPDATE bookings
    SET status = 'completed'
    WHERE status = 'upcoming' AND check_out < CURDATE()
");

// ---------------------------------------------------------
// هات كل حجوزات اليوزر مع بيانات الشقة (JOIN)
// ---------------------------------------------------------
$stmt = $conn->prepare("
    SELECT
        b.id, b.check_in, b.check_out, b.guests,
        b.nights, b.total_price, b.status,
        p.title AS property_title, p.id AS property_id
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    WHERE b.user_id = ?
    ORDER BY b.check_in DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [
    'upcoming'  => [],
    'completed' => [],
    'cancelled' => [],
];

while ($row = $result->fetch_assoc()) {
    $bookings[$row['status']][] = $row;
}
$stmt->close();

$statusLabels = [
    'upcoming'  => 'القادمة',
    'completed' => 'المكتملة',
    'cancelled' => 'الملغاة',
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حجوزاتي</title>
    <style>
        .cancel-btn {
    background: #ef4444;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-size: 13px;
}
.review-btn {
    background: #f59e0b;
    color: #fff;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
}
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; background: #f9fafb; }
        h1 { text-align: center; }
        .tabs { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: 1px solid #ddd; background: #fff; cursor: pointer; }
        .tab-btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
        .section { display: none; }
        .section.active { display: block; }
        .booking-card { background: #fff; border-radius: 10px; padding: 18px; margin-bottom: 15px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; }
        .booking-info h3 { margin: 0 0 8px; }
        .booking-info p { margin: 3px 0; color: #555; font-size: 14px; }
        .price { font-weight: bold; color: #2563eb; font-size: 16px; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; margin-top: 5px; }
        .status.upcoming { background: #dbeafe; color: #1e40af; }
        .status.completed { background: #dcfce7; color: #166534; }
        .status.cancelled { background: #fee2e2; color: #991b1b; }
        .cancel-btn { background: #ef4444; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 13px; }
        .empty { text-align: center; color: #999; padding: 30px; }
    </style>
</head>
<body>

<h1>حجوزاتي</h1>

<div class="tabs">
    <?php $first = true; foreach ($statusLabels as $key => $label): ?>
        <button class="tab-btn <?= $first ? 'active' : '' ?>" onclick="showTab('<?= $key ?>')">
            <?= $label ?> (<?= count($bookings[$key]) ?>)
        </button>
    <?php $first = false; endforeach; ?>
</div>

<?php $first = true; foreach ($statusLabels as $key => $label): ?>
    <div class="section <?= $first ? 'active' : '' ?>" id="section-<?= $key ?>">
        <?php if (empty($bookings[$key])): ?>
            <p class="empty">لا يوجد حجوزات <?= $label ?>.</p>
        <?php else: ?>
            <?php foreach ($bookings[$key] as $b): ?>
                <div class="booking-card">
                    <div class="booking-info">
                        <h3><?= htmlspecialchars($b['property_title']) ?></h3>
                        <p>📅 <?= htmlspecialchars($b['check_in']) ?> → <?= htmlspecialchars($b['check_out']) ?> (<?= $b['nights'] ?> ليالي)</p>
                        <p>👥 <?= $b['guests'] ?> أفراد</p>
                        <p class="price"><?= number_format($b['total_price'], 2) ?> EGP</p>
                        <span class="status <?= $b['status'] ?>"><?= $statusLabels[$b['status']] ?></span>
                    </div>
                   <?php if ($b['status'] === 'upcoming'): ?>

    <a href="cancel.php?booking_id=<?= $b['id'] ?>"
       class="cancel-btn"
       onclick="return confirm('هل أنتِ متأكدة من إلغاء هذا الحجز؟');">
        إلغاء
    </a>

<?php elseif ($b['status'] === 'completed'): ?>

   <a href="../reviews/add.php?booking_id=<?= $b['id'] ?>"
   class="review-btn">
    ⭐️ اكتب تقييم
</a>

<?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php $first = false; endforeach; ?>

<script>
function showTab(key) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('section-' + key).classList.add('active');
    event.target.classList.add('active');
}
</script>

</body>
</html>