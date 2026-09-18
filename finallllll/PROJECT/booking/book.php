<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_guard.php';

require_login();

// ---------------------------------------------------------
// 1) لازم يكون اليوزر مسجل دخول
// ---------------------------------------------------------
$user_id = $_SESSION['user_id'];
$errors  = [];
$success = false;

// ---------------------------------------------------------
// 2) هات بيانات الشقة (property_id جاية من الرابط GET)
// ---------------------------------------------------------
$property_id = isset($_GET['property_id']) ? (int) $_GET['property_id'] : (int) ($_POST['property_id'] ?? 0);

if ($property_id <= 0) {
    die('لا يوجد عقار محدد.');
}

$stmt = $conn->prepare("SELECT id, title, price_per_night, max_guests FROM properties WHERE id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$property) {
    die('العقار غير موجود.');
}

// ---------------------------------------------------------
// 3) لما اليوزر يبعت الفورم (POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $check_in  = $_POST['check_in']  ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $guests    = (int) ($_POST['guests'] ?? 0);

    // --- Validation أساسي ---
    if (empty($check_in) || empty($check_out)) {
        $errors[] = 'من فضلك اختاري تاريخ الوصول والمغادرة.';
    } elseif (strtotime($check_in) < strtotime(date('Y-m-d'))) {
        $errors[] = 'تاريخ الوصول لا يمكن أن يكون في الماضي.';
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $errors[] = 'تاريخ المغادرة لازم يكون بعد تاريخ الوصول.';
    }

    if ($guests <= 0) {
        $errors[] = 'من فضلك حددي عدد الأفراد.';
    } elseif ($guests > $property['max_guests']) {
        $errors[] = 'عدد الأفراد أكبر من الحد المسموح لهذا العقار (' . $property['max_guests'] . ').';
    }

    // --- حساب عدد الليالي والسعر الإجمالي ---
    $nights      = 0;
    $total_price = 0;

    if (empty($errors)) {
        $date1  = new DateTime($check_in);
        $date2  = new DateTime($check_out);
        $nights = $date2->diff($date1)->days;

        if ($nights <= 0) {
            $errors[] = 'المدة المحددة غير صحيحة.';
        } else {
            $total_price = $nights * $property['price_per_night'];
        }
    }

    // --- التحقق من التعارض في التواريخ (Availability) ---
    if (empty($errors)) {
        $availStmt = $conn->prepare("
            SELECT id FROM bookings
            WHERE property_id = ?
              AND status != 'cancelled'
              AND check_in < ?
              AND check_out > ?
        ");
        $availStmt->bind_param('iss', $property_id, $check_out, $check_in);
        $availStmt->execute();
        $conflict = $availStmt->get_result()->fetch_assoc();
        $availStmt->close();

        if ($conflict) {
            $errors[] = 'العقار محجوز بالفعل في هذه الفترة، من فضلك اختاري تواريخ أخرى.';
        }
    }

    // --- إنشاء الحجز ---
    if (empty($errors)) {
        $insertStmt = $conn->prepare("
            INSERT INTO bookings
                (user_id, property_id, check_in, check_out, guests, price_per_night, nights, total_price, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'upcoming')
        ");
        $insertStmt->bind_param(
            'iissidid',
            $user_id,
            $property_id,
            $check_in,
            $check_out,
            $guests,
            $property['price_per_night'],
            $nights,
            $total_price
        );

        if ($insertStmt->execute()) {
            $newBookingId = $conn->insert_id;
            $insertStmt->close();
            header('Location: confirmation.php?booking_id=' . $newBookingId);
            exit;
        } else {
            $errors[] = 'حدث خطأ أثناء إنشاء الحجز، حاولي مرة أخرى.';
        }
        $insertStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حجز <?= htmlspecialchars($property['title']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 40px auto; }
        .box { border: 1px solid #ddd; border-radius: 10px; padding: 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        .error { color: #b91c1c; background: #fee2e2; padding: 10px; border-radius: 6px; margin-top: 10px; }
        .summary { background: #f3f4f6; padding: 15px; border-radius: 8px; margin-top: 15px; }
    </style>
</head>
<body>

<h2><?= htmlspecialchars($property['title']) ?></h2>
<p>السعر لليلة الواحدة: <?= number_format($property['price_per_night'], 2) ?> EGP</p>

<?php if (!empty($errors)): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="box">
    <form method="POST" action="">
        <input type="hidden" name="property_id" value="<?= $property_id ?>">

        <label>تاريخ الوصول (Check-in)</label>
        <input type="date" name="check_in" value="<?= htmlspecialchars($_POST['check_in'] ?? '') ?>" required>

        <label>تاريخ المغادرة (Check-out)</label>
        <input type="date" name="check_out" value="<?= htmlspecialchars($_POST['check_out'] ?? '') ?>" required>

        <label>عدد الأفراد (Guests)</label>
        <input type="number" name="guests" min="1" max="<?= $property['max_guests'] ?>"
               value="<?= htmlspecialchars($_POST['guests'] ?? '1') ?>" required>

        <button type="submit">تأكيد الحجز</button>
    </form>
</div>

<script>
// حساب تلقائي للمدة والسعر الإجمالي في الفرونت (للعرض فقط، الحساب الحقيقي بيتم في PHP)
const checkIn  = document.querySelector('[name=check_in]');
const checkOut = document.querySelector('[name=check_out]');
const pricePerNight = <?= (float) $property['price_per_night'] ?>;

function updateSummary() {
    if (checkIn.value && checkOut.value) {
        const d1 = new Date(checkIn.value);
        const d2 = new Date(checkOut.value);
        const nights = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));

        let existing = document.querySelector('.summary');
        if (existing) existing.remove();

        if (nights > 0) {
            const total = nights * pricePerNight;
            const div = document.createElement('div');
            div.className = 'summary';
            div.innerHTML = `عدد الليالي: <b>${nights}</b><br>الإجمالي: <b>${total.toLocaleString()} EGP</b>`;
            document.querySelector('.box').appendChild(div);
        }
    }
}

checkIn.addEventListener('change', updateSummary);
checkOut.addEventListener('change', updateSummary);
</script>

</body>
</html>