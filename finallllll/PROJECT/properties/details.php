<?php
session_start();

require_once "../config/database.php";

/* Get property ID from URL */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid property ID.");
}

$property_id = intval($_GET['id']);


/* Get property information */

$sql = "SELECT * FROM properties WHERE id = $property_id";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Property not found.");
}

$property = $result->fetch_assoc();


/* Get property images */

$image_sql = "SELECT image 
              FROM property_images 
              WHERE property_id = $property_id";

$image_result = $conn->query($image_sql);


/* Get property amenities */

$amenity_sql = "SELECT amenities.name
                FROM amenities
                INNER JOIN property_amenities
                ON amenities.id = property_amenities.amenity_id
                WHERE property_amenities.property_id = $property_id";

$amenity_result = $conn->query($amenity_sql);

$reviewsStmt = $conn->prepare("
    SELECT r.id, r.rating, r.comment, r.created_at,
           u.first_name, u.last_name, r.user_id
    FROM reviews r
    INNER JOIN users u ON u.id = r.user_id
    WHERE r.property_id = ?
    ORDER BY r.created_at DESC
");
$reviewsStmt->bind_param('i', $property_id);
$reviewsStmt->execute();
$reviews_result = $reviewsStmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($property['title']); ?> - StayEase
    </title>

    <link rel="stylesheet" href="../assets/css/properties.css">

</head>


<body>


<!-- ================= NAVBAR ================= -->

<?php require_once __DIR__ . "/../includes/navbar.php"; ?>



<!-- ================= PROPERTY DETAILS ================= -->

<main class="details-container">


    <a href="index.php" class="back-link">
        ← Back to Properties
    </a>


    <!-- TITLE -->

    <div class="details-header">

        <div>

            <span class="property-type">

                <?php
                echo htmlspecialchars($property['property_type']);
                ?>

            </span>

            <h1>

                <?php
                echo htmlspecialchars($property['title']);
                ?>

            </h1>

            <p class="location">

                
                <?php
                echo htmlspecialchars($property['location']);
                ?>

            </p>

        </div>


        <div class="details-price">

            <strong>

                <?php
                echo htmlspecialchars($property['price_per_night']);
                ?>

                EGP

            </strong>

            <span>
                / night
            </span>

        </div>

    </div>



    <!-- ================= IMAGES ================= -->

    <div class="details-gallery">

        <?php

        if ($image_result->num_rows > 0) {

            while ($image = $image_result->fetch_assoc()) {

        ?>

            <img
                src="../assets/images/<?php echo htmlspecialchars($image['image']); ?>"
                alt="<?php echo htmlspecialchars($property['title']); ?>"
            >

        <?php

            }

        } else {

        ?>

            <div class="no-image">
            
            </div>

        <?php

        }

        ?>

    </div>
    <!-- ================= MAP ================= -->

<section class="property-map">

    <h2>Location</h2>

    <iframe
        src="https://www.openstreetmap.org/export/embed.html?bbox=<?php echo ($property['longitude'] - 0.03); ?>%2C<?php echo ($property['latitude'] - 0.03); ?>%2C<?php echo ($property['longitude'] + 0.03); ?>%2C<?php echo ($property['latitude'] + 0.03); ?>&layer=mapnik&marker=<?php echo $property['latitude']; ?>%2C<?php echo $property['longitude']; ?>"
        width="100%"
        height="400"
        style="border: 1px solid black; border-radius: 15px;"
        loading="lazy">
    </iframe>

</section>



    <!-- ================= CONTENT ================= -->

    <div class="details-content">


        <!-- LEFT SIDE -->

        <div class="details-main">


            <h2>
                About this property
            </h2>

            <p class="description">

                <?php
                echo htmlspecialchars($property['description']);
                ?>

            </p>



            <!-- FEATURES -->

            <h2>
                Property Information
            </h2>


            <div class="details-features">

                <div>
                    
                    <strong>
                        <?php echo $property['bedrooms']; ?>
                    </strong>
                    Bedrooms
                </div>


                <div>
                    
                    <strong>
                        <?php echo $property['bathrooms']; ?>
                    </strong>
                    Bathrooms
                </div>


                <div>
                    
                    <strong>
                        <?php echo $property['max_guests']; ?>
                    </strong>
                    Guests
                </div>


                <div>
            
                    <strong>
                        <?php echo htmlspecialchars($property['property_type']); ?>
                    </strong>
                </div>

            </div>



            <!-- AMENITIES -->

            <h2>
                Amenities
            </h2>


            <div class="amenities-list">

                <?php

                if ($amenity_result->num_rows > 0) {

                    while ($amenity = $amenity_result->fetch_assoc()) {

                ?>

                    <div class="amenity">

                        ✓

                        <?php
                        echo htmlspecialchars($amenity['name']);
                        ?>

                    </div>

                <?php

                    }

                } else {

                    echo "<p>No amenities available.</p>";

                }

                ?>

            </div>


        </div>

        <!-- REVIEWS -->
        <section class="reviews-section" style="grid-column: 1 / -1; margin-top: 30px;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:15px;">
                <h2 style="margin:0;">Reviews</h2>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../reviews/add.php?property_id=<?= $property_id ?>" class="details-btn">Write a Review</a>
                <?php else: ?>
                    <a href="../login.php" class="details-btn">Login to Review</a>
                <?php endif; ?>
            </div>

            <?php if ($reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <article style="border-top:1px solid #e5e7eb; padding:16px 0;">
                        <strong><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></strong>
                        <span style="margin-inline:8px;"> <?= str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5 - (int)$review['rating']) ?> </span>
                        <small style="color:#6b7280;"><?= htmlspecialchars($review['created_at']) ?></small>
                        <?php if (!empty($review['comment'])): ?>
                            <p><?= htmlspecialchars($review['comment']) ?></p>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id']) && ((int)$_SESSION['user_id'] === (int)$review['user_id'] || ($_SESSION['role'] ?? '') === 'admin')): ?>
                            <a href="../reviews/delete.php?review_id=<?= (int)$review['id'] ?>"
                               onclick="return confirm('هل أنت متأكد من حذف التقييم؟');">Delete</a>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews yet.</p>
            <?php endif; ?>
        </section>

        <!-- RIGHT SIDE -->

        <aside class="booking-box">

            <h2>
                Ready to book?
            </h2>

            <p>
                <?php echo $property['price_per_night']; ?>
                EGP / night
            </p>


            <a
                href="../booking/book.php?property_id=<?php echo $property['id']; ?>"
                class="reserve-btn"
            >
                Reserve this property
            </a>

        </aside>


    </div>


</main>



</body>

</html>