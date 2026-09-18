<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_admin();

$errors  = [];
$success = '';

// ---------------------------------------------------------
// حذف شقة
// ---------------------------------------------------------
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $stmt->close();
    header('Location: properties.php?deleted=1');
    exit;
}

// ---------------------------------------------------------
// إضافة / تعديل شقة (نفس الفورم لعمليتين)
// ---------------------------------------------------------
$editProperty = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editProperty = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title           = trim($_POST['title'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $location        = trim($_POST['location'] ?? '');
    $price_per_night = (float) ($_POST['price_per_night'] ?? 0);
    $bedrooms        = (int) ($_POST['bedrooms'] ?? 0);
    $bathrooms       = (int) ($_POST['bathrooms'] ?? 0);
    $max_guests      = (int) ($_POST['max_guests'] ?? 0);
    $property_type   = trim($_POST['property_type'] ?? '');
    $property_id     = (int) ($_POST['property_id'] ?? 0);

    if (empty($title) || empty($location) || $price_per_night <= 0) {
        $errors[] = 'من فضلك املأ العنوان والموقع والسعر بشكل صحيح.';
    }

    if (empty($errors)) {
        if ($property_id > 0) {
            // تعديل
            $stmt = $conn->prepare("
                UPDATE properties SET
                    title = ?, description = ?, location = ?, price_per_night = ?,
                    bedrooms = ?, bathrooms = ?, max_guests = ?, property_type = ?
                WHERE id = ?
            ");
            $stmt->bind_param(
                'sssdiiisi',
                $title, $description, $location, $price_per_night,
                $bedrooms, $bathrooms, $max_guests, $property_type, $property_id
            );
        } else {
            // إضافة جديدة
            $stmt = $conn->prepare("
                INSERT INTO properties
                    (title, description, location, price_per_night, bedrooms, bathrooms, max_guests, property_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'sssdiiis',
                $title, $description, $location, $price_per_night,
                $bedrooms, $bathrooms, $max_guests, $property_type
            );
        }

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: properties.php?saved=1');
            exit;
        } else {
            $errors[] = 'حدث خطأ أثناء الحفظ.';
        }
    }
}

// ---------------------------------------------------------
// هات كل الشقق
// ---------------------------------------------------------
$properties = $conn->query("SELECT * FROM properties ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الشقق - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .navbar { background: #111827; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #d1d5db; text-decoration: none; margin-right: 20px; }
        .navbar a:hover { color: #fff; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .form-box { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 6px; }
        button { margin-top: 15px; padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-size: 14px; }
        th { background: #f9fafb; }
        .actions a { margin-left: 10px; text-decoration: none; }
        .edit-link { color: #2563eb; }
        .delete-link { color: #ef4444; }
        .msg { padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .msg.success { background: #dcfce7; color: #166534; }
        .msg.error { background: #fee2e2; color: #991b1b; }
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
    <h1>إدارة الشقق</h1>

    <?php if (isset($_GET['saved'])): ?>
        <div class="msg success">تم الحفظ بنجاح.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="msg success">تم الحذف بنجاح.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="msg error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h3><?= $editProperty ? 'تعديل شقة' : 'إضافة شقة جديدة' ?></h3>
        <form method="POST" action="">
            <input type="hidden" name="property_id" value="<?= $editProperty['id'] ?? 0 ?>">
            <div class="form-grid">
                <div>
                    <label>العنوان</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($editProperty['title'] ?? '') ?>" required>
                </div>
                <div>
                    <label>الموقع</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($editProperty['location'] ?? '') ?>" required>
                </div>
                <div>
                    <label>السعر لليلة (EGP)</label>
                    <input type="number" step="0.01" name="price_per_night" value="<?= htmlspecialchars($editProperty['price_per_night'] ?? '') ?>" required>
                </div>
                <div>
                    <label>نوع العقار</label>
                    <input type="text" name="property_type" value="<?= htmlspecialchars($editProperty['property_type'] ?? '') ?>" placeholder="apartment / villa / studio">
                </div>
                <div>
                    <label>عدد الغرف</label>
                    <input type="number" name="bedrooms" value="<?= htmlspecialchars($editProperty['bedrooms'] ?? '') ?>">
                </div>
                <div>
                    <label>عدد الحمامات</label>
                    <input type="number" name="bathrooms" value="<?= htmlspecialchars($editProperty['bathrooms'] ?? '') ?>">
                </div>
                <div>
                    <label>أقصى عدد أفراد</label>
                    <input type="number" name="max_guests" value="<?= htmlspecialchars($editProperty['max_guests'] ?? '') ?>">
                </div>
            </div>
            <div style="margin-top:12px;">
                <label>الوصف</label>
                <textarea name="description" rows="3"><?= htmlspecialchars($editProperty['description'] ?? '') ?></textarea>
            </div>
            <button type="submit"><?= $editProperty ? 'حفظ التعديلات' : 'إضافة الشقة' ?></button>
            <?php if ($editProperty): ?>
                <a href="properties.php" style="margin-right:10px;">إلغاء</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>العنوان</th>
                <th>الموقع</th>
                <th>السعر/ليلة</th>
                <th>التقييم</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $properties->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= htmlspecialchars($p['location']) ?></td>
                    <td><?= number_format($p['price_per_night'], 2) ?> EGP</td>
                    <td><?= $p['rating'] ? number_format($p['rating'], 1) . ' ⭐ (' . $p['review_count'] . ')' : 'لا يوجد' ?></td>
                    <td class="actions">
                        <a class="edit-link" href="properties.php?edit=<?= $p['id'] ?>">تعديل</a>
                        <a class="delete-link" href="properties.php?delete=<?= $p['id'] ?>"
                           onclick="return confirm('هل أنتِ متأكدة من حذف هذه الشقة؟');">حذف</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>