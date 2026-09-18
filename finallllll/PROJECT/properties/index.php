<?php

require_once "../config/database.php";

$sql = "SELECT * FROM properties ORDER BY id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Properties - StayEase</title>

    <link rel="stylesheet" href="../assets/css/properties.css">

</head>

<body>

    <!-- Hero (Navbar + Welcome section share the same background) -->
    <div class="hero-wrap">

        <?php require_once __DIR__ . "/../includes/navbar.php"; ?>

        <section class="hero-content">
            <p class="hero-eyebrow">StayEase</p>
            <h1>Welcome to StayEase</h1>
            <p>Find comfortable apartments, studios and villas, wherever your next trip takes you.</p>
            <a href="#contact" class="hero-btn">Contact Us</a>
        </section>

    </div>


    <!-- Page Header -->

    <section class="page-header">
    

        <h1>Find Your Perfect Stay</h1>

        <p>
            Discover comfortable apartments, studios and villas.
        </p>
        <a href="search.php" class="search-page-btn">
     Search & Filter Properties
</a>

    </section>


    <!-- Properties -->

    <main class="properties-container">

        <?php

        if ($result->num_rows > 0) {

            while ($property = $result->fetch_assoc()) {

        ?>

                <div class="property-card">

                    <!-- Image -->

                    <div class="property-image">

    <?php

    $property_id = $property['id'];

    $image_sql = "SELECT image 
                  FROM property_images 
                  WHERE property_id = $property_id 
                  LIMIT 1";

    $image_result = $conn->query($image_sql);

    if ($image_result->num_rows > 0) {

        $image = $image_result->fetch_assoc();

    ?>

        <img
            src="../assets/images/<?php echo htmlspecialchars($image['image']); ?>"
            alt="<?php echo htmlspecialchars($property['title']); ?>"
        >

    <?php

    } else {

    ?>

        <div class="image-placeholder">
    
        </div>

    <?php

    }

    ?>

</div>


                    <!-- Property Information -->

                    <div class="property-info">

                        <span class="property-type">
                            <?php echo htmlspecialchars($property['property_type']); ?>
                        </span>

                        <h2>
                            <?php echo htmlspecialchars($property['title']); ?>
                        </h2>

                        <p class="location">
                            📍 <?php echo htmlspecialchars($property['location']); ?>
                        </p>

                        <div class="property-features">

                            <span>
                                🛏 <?php echo htmlspecialchars($property['bedrooms']); ?> Bedrooms
                            </span>

                            <span>
                                🚿 <?php echo htmlspecialchars($property['bathrooms']); ?> Bathrooms
                            </span>

                            <span>
                                👥 <?php echo htmlspecialchars($property['max_guests']); ?> Guests
                            </span>

                        </div>


                        <div class="card-bottom">

                            <div class="price">

                                <strong>
                                    <?php echo htmlspecialchars($property['price_per_night']); ?>
                                    EGP
                                </strong>

                                <span>/ night</span>

                            </div>


                            <a
                                href="details.php?id=<?php echo $property['id']; ?>"
                                class="details-btn"
                            >
                                View Details
                            </a>

                        </div>

                    </div>

                </div>

        <?php

            }

        } else {

            echo "<p>No properties found.</p>";

        }

        ?>

    </main>



</body>

</html>