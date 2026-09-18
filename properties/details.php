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
    <style>

.reviews-section {
    grid-column: 1 / -1;
    margin-top: 40px;
}

.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 15px;
}

.reviews-header h2 {
    margin: 0;
    font-size: 28px;
    color: #222;
}

.reviews-count {
    margin: 5px 0 0;
    color: #777;
    font-size: 14px;
}

.review-write-btn {
    background: #8e7754;
    color: white;
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 8px;
    font-size: 14px;
    transition: 0.3s;
}

.review-write-btn:hover {
    opacity: 0.85;
}

.reviews-summary {
    background: #faf8f5;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
}

.average-rating {
    display: flex;
    align-items: center;
    gap: 15px;
}

.rating-number {
    font-size: 35px;
    font-weight: bold;
    color: #333;
}

.average-stars {
    color: #f5a623;
    font-size: 22px;
    letter-spacing: 2px;
}

.rating-label {
    color: #777;
    font-size: 14px;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.review-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
}

.review-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.review-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #8e7754;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.review-user strong {
    display: block;
    color: #333;
    font-size: 15px;
}

.review-user small {
    display: block;
    margin-top: 4px;
    color: #999;
    font-size: 12px;
}

.review-stars {
    color: #f5a623;
    font-size: 18px;
    letter-spacing: 2px;
}

.review-comment {
    margin: 18px 0 10px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    color: #555;
    line-height: 1.7;
    font-size: 14px;
}

.review-delete {
    color: #dc2626;
    font-size: 12px;
    text-decoration: none;
}

.review-delete:hover {
    text-decoration: underline;
}

.no-reviews {
    text-align: center;
    background: #fafafa;
    border: 1px dashed #ddd;
    border-radius: 14px;
    padding: 40px 20px;
}

.no-reviews-icon {
    font-size: 35px;
    margin-bottom: 10px;
}

.no-reviews h3 {
    margin: 5px 0;
    color: #444;
}

.no-reviews p {
    color: #888;
    font-size: 14px;
}

@media (max-width: 600px) {

    .reviews-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .review-write-btn {
        width: 100%;
        text-align: center;
    }

    .review-top {
        align-items: flex-start;
        flex-direction: column;
    }

    .average-rating {
        flex-wrap: wrap;
    }

}

</style>

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

       <!-- ================= REVIEWS ================= -->

<section class="reviews-section">

    <div class="reviews-header">

        <div>
            <h2>Reviews</h2>

            <?php if ((int)$property['review_count'] > 0): ?>
                <p class="reviews-count">
                    <?= (int)$property['review_count'] ?> reviews
                </p>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="../reviews/add.php?property_id=<?= $property_id ?>"
               class="review-write-btn">
                ⭐ Write a Review
            </a>

        <?php else: ?>

            <a href="../login.php" class="review-write-btn">
                Login to Review
            </a>

        <?php endif; ?>

    </div>


    <?php if ($reviews_result->num_rows > 0): ?>

        <div class="reviews-summary">

            <div class="average-rating">

                <span class="rating-number">
                    <?= number_format((float)$property['rating'], 1) ?>
                </span>

                <div class="average-stars">
                    <?php
                    $averageRating = round((float)$property['rating']);
                    echo str_repeat('★', $averageRating);
                    echo str_repeat('☆', 5 - $averageRating);
                    ?>
                </div>

                <span class="rating-label">
                    Overall Rating
                </span>

            </div>

        </div>


        <div class="reviews-list">

            <?php while ($review = $reviews_result->fetch_assoc()): ?>

                <article class="review-card">

                    <div class="review-top">

                        <div class="review-user">

                            <div class="user-avatar">
                                <?= strtoupper(substr($review['first_name'], 0, 1)) ?>
                            </div>

                            <div>
                                <strong>
                                    <?= htmlspecialchars(
                                        $review['first_name'] . ' ' . $review['last_name']
                                    ) ?>
                                </strong>

                                <small>
                                    <?= date(
                                        'd M Y',
                                        strtotime($review['created_at'])
                                    ) ?>
                                </small>
                            </div>

                        </div>


                        <div class="review-stars">

                            <?= str_repeat(
                                '★',
                                (int)$review['rating']
                            ) ?>

                            <?= str_repeat(
                                '☆',
                                5 - (int)$review['rating']
                            ) ?>

                        </div>

                    </div>


                    <?php if (!empty($review['comment'])): ?>

                        <p class="review-comment">
                            <?= htmlspecialchars($review['comment']) ?>
                        </p>

                    <?php endif; ?>


                    <?php
                    if (
                        isset($_SESSION['user_id']) &&
                        (
                            (int)$_SESSION['user_id'] === (int)$review['user_id']
                            ||
                            ($_SESSION['role'] ?? '') === 'admin'
                        )
                    ):
                    ?>

                        <a
                            href="../reviews/delete.php?review_id=<?= (int)$review['id'] ?>"
                            class="review-delete"
                            onclick="return confirm('هل أنت متأكد من حذف التقييم؟');"
                        >
                            Delete
                        </a>

                    <?php endif; ?>
                   </article>

            <?php endwhile; ?>

        </div>


    <?php else: ?>

        <div class="no-reviews">

            <div class="no-reviews-icon">
                ⭐
            </div>

            <h3>No reviews yet</h3>

            <p>
                Be the first guest to share your experience.
            </p>

        </div>

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