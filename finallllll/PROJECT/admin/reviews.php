<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_admin();

// ---------------------------------------------------------
// هات كل التقييمات مع اسم اليوزر والشقة
// ---------------------------------------------------------
$reviews = $conn->query("
    SELECT
        r.id, r.rating, r.comment, r.created_at,
        u.first_name, u.last_name,
        p.title AS property_title
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN properties p ON r.property_id = p.id
    ORDER BY r.id DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة التقييمات - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .navbar { background: #111827; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #d1d5db; text-decoration: none; margin-right: 20px; }
        .navbar a:hover { color: #fff; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .review-card { background: #fff; border-radius: 10px; padding: 15px 20px; margin-bottom: 12px; }
        .review-top { display: flex; justify-content: space-between; align-items: center; }
        .stars { color: #f59e0b; }
        .property-name { color: #2563eb; font-weight: bold; margin-top: 4px; }
        .comment { margin-top: 8px; color: #374151; }
        .meta { font-size: 12px; color: #9ca3af; margin-top: 8px; }
        .delete-link { color: #ef4444; text-decoration: none; }
        .msg { padding: 10px; border-radius: 6px; margin-bottom: 15px; background: #dcfce7; color: #166534; }
        .empty { text-align: center; color: #999; padding: 30px; }
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
    <h1>إدارة التقييمات</h1>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="msg">تم حذف التقييم بنجاح.</div>
    <?php endif; ?>

    <?php if ($reviews->num_rows === 0): ?>
        <p class="empty">لا يوجد تقييمات حتى الآن.</p>
    <?php else: ?>
        <?php while ($r = $reviews->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-top">
                    <div>
                        <strong><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></strong>
                        <span class="stars"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></span>
                    </div>
                    <a class="delete-link" href="../reviews/delete.php?review_id=<?= $r['id'] ?>"
                       onclick="return confirm('هل أنتِ متأكدة من حذف هذا التقييم؟');">حذف</a>
                </div>
                <div class="property-name">📍 <?= htmlspecialchars($r['property_title']) ?></div>
                <?php if (!empty($r['comment'])): ?>
                    <div class="comment"><?= htmlspecialchars($r['comment']) ?></div>
                <?php endif; ?>
                <div class="meta"><?= htmlspecialchars($r['created_at']) ?></div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>