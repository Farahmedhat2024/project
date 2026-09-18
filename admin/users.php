<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_admin();

// ---------------------------------------------------------
// حذف مستخدم
// ---------------------------------------------------------
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    if ($deleteId !== (int) $_SESSION['user_id']) { // ما يحذفش نفسه
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $deleteId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: users.php?deleted=1');
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المستخدمين - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .navbar { background: #111827; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #d1d5db; text-decoration: none; margin-right: 20px; }
        .navbar a:hover { color: #fff; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-size: 14px; }
        th { background: #f9fafb; }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; }
        .badge.admin { background: #dbeafe; color: #1e40af; }
        .badge.user { background: #f3f4f6; color: #374151; }
        .actions a { margin-left: 10px; text-decoration: none; }
        .delete-link { color: #ef4444; }
        .toggle-link { color: #f59e0b; }
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
    <h1>إدارة المستخدمين</h1>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="msg">تم التحديث بنجاح.</div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الإيميل</th>
                <th>الدور</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge <?= $u['role'] === 'admin' ? 'admin' : 'user' ?>">
                            <?= htmlspecialchars($u['role']) ?>
                        </span>
                    </td>
                    <td class="actions">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a class="delete-link" href="users.php?delete=<?= $u['id'] ?>"
                               onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">حذف</a>
                        <?php else: ?>
                            <em>(أنت)</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>