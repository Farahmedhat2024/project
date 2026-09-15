<?php

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

$image_sql = "SELECT image_url
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



/* Get property reviews */

$review_sql = "SELECT reviews.*, users.name
               FROM reviews
               INNER JOIN users
               ON reviews.user_id = users.id
               WHERE reviews.property_id = $property_id
               ORDER BY reviews.created_at DESC";

$review_result = $conn->query($review_sql);


/* Get average rating */

$rating_sql = "SELECT AVG(rating) AS average_rating
               FROM reviews
               WHERE property_id = $property_id";

$rating_result = $conn->query($rating_sql);

$rating_data = $rating_result->fetch_assoc();

$average_rating = $rating_data['average_rating'];

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($property['titel']); ?> - StayEase
    </title>

    <link rel="stylesheet" href="../assets/css/properties.css">

</head>


<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="logo">
        StayEase
    </div>

    <div class="nav-links">

        <a href="../index.php">Home</a>

        <a href="index.php">Properties</a>

        <a href="#">Login</a>

    </div>

</nav>



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
                echo htmlspecialchars($property['titel']);
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
            src="../assets/images/<?php echo htmlspecialchars($image['image_url']); ?>"
            alt="<?php echo htmlspecialchars($property['titel']); ?>"
        >

    <?php

        }

    } else {

    ?>

        <div class="no-image">
            No images available.
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


            <!-- ================= REVIEWS ================= -->

<section class="reviews-section">

    <h2>Reviews</h2>

    <!-- Average Rating -->

    <div class="average-rating">

        <?php if ($average_rating !== null) { ?>

            <strong>
                <?php echo number_format($average_rating, 1); ?> / 5
            </strong>

            ⭐️

        <?php } else { ?>

            <p>No ratings yet.</p>

        <?php } ?>

    </div>


    <!-- Reviews List -->

    <?php if ($review_result->num_rows > 0) { ?>

        <?php while ($review = $review_result->fetch_assoc()) { ?>

            <div class="review">

                <h3>
                    <?php echo htmlspecialchars($review['name']); ?>
                </h3>

                <p>
                    Rating:
                    <?php echo $review['rating']; ?> / 5 ⭐️
                </p>

                <p>
                    <?php echo htmlspecialchars($review['comment']); ?>
                </p>

                <small>
                    <?php echo $review['created_at']; ?>
                </small>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>No reviews yet.</p>

    <?php } ?>

</section>



        </div>



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