<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_admin();

// ---------------------------------------------------------

// ---------------------------------------------------------
// هات الإحصائيات
// ---------------------------------------------------------
$totalUsers      = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalProperties = $conn->query("SELECT COUNT(*) AS c FROM properties")->fetch_assoc()['c'];
$totalBookings   = $conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'];
$totalReviews    = $conn->query("SELECT COUNT(*) AS c FROM reviews")->fetch_assoc()['c'];

$upcomingBookings  = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'upcoming'")->fetch_assoc()['c'];
$completedBookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'completed'")->fetch_assoc()['c'];
$cancelledBookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'cancelled'")->fetch_assoc()['c'];

$totalRevenue = $conn->query("
    SELECT COALESCE(SUM(total_price), 0) AS total
    FROM bookings WHERE status != 'cancelled'
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .navbar { background: #111827; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #d1d5db; text-decoration: none; margin-right: 20px; }
        .navbar a:hover { color: #fff; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h1 { margin-bottom: 25px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); text-align: center; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #2563eb; }
        .stat-card .label { color: #6b7280; margin-top: 5px; }
        .section-title { margin-top: 40px; margin-bottom: 15px; }
        .mini-stats { display: flex; gap: 15px; flex-wrap: wrap; }
        .mini-stat { background: #fff; border-radius: 8px; padding: 15px 20px; flex: 1; min-width: 140px; text-align: center; }
        .mini-stat.upcoming { border-top: 3px solid #3b82f6; }
        .mini-stat.completed { border-top: 3px solid #16a34a; }
        .mini-stat.cancelled { border-top: 3px solid #ef4444; }
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
    <h1>نظرة عامة</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= $totalUsers ?></div>
            <div class="label">إجمالي المستخدمين</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $totalProperties ?></div>
            <div class="label">إجمالي الشقق</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $totalBookings ?></div>
            <div class="label">إجمالي الحجوزات</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $totalReviews ?></div>
            <div class="label">إجمالي التقييمات</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= number_format($totalRevenue, 0) ?></div>
            <div class="label">إجمالي الإيرادات (EGP)</div>
        </div>
    </div>

    <h2 class="section-title">حالة الحجوزات</h2>
    <div class="mini-stats">
        <div class="mini-stat upcoming">
            <div class="number"><?= $upcomingBookings ?></div>
            <div>قادمة</div>
        </div>
        <div class="mini-stat completed">
            <div class="number"><?= $completedBookings ?></div>
            <div>مكتملة</div>
        </div>
        <div class="mini-stat cancelled">
            <div class="number"><?= $cancelledBookings ?></div>
            <div>ملغاة</div>
        </div>
    </div>
</div>

</body>
</html>