<?php

require_once "../config/database.php";


/* =========================
   GET FILTER VALUES
========================= */

$location = isset($_GET['location'])
    ? trim($_GET['location'])
    : '';

$min_price = isset($_GET['min_price'])
    ? trim($_GET['min_price'])
    : '';

$max_price = isset($_GET['max_price'])
    ? trim($_GET['max_price'])
    : '';

$bedrooms = isset($_GET['bedrooms'])
    ? trim($_GET['bedrooms'])
    : '';

$guests = isset($_GET['guests'])
    ? trim($_GET['guests'])
    : '';

$property_type = isset($_GET['property_type'])
    ? trim($_GET['property_type'])
    : '';

$sort = isset($_GET['sort'])
    ? trim($_GET['sort'])
    : '';


/* =========================
   AMENITIES
========================= */

$selected_amenities = isset($_GET['amenities'])
    ? $_GET['amenities']
    : [];

if (!is_array($selected_amenities)) {
    $selected_amenities = [];
}


/* =========================
   BUILD SQL QUERY
========================= */

$sql = "SELECT DISTINCT properties.*
        FROM properties
        WHERE 1=1";


/* =========================
   LOCATION SEARCH
========================= */

if ($location !== '') {

    $safe_location = $conn->real_escape_string($location);

    $sql .= " AND (
                properties.location LIKE '%$safe_location%'
                OR properties.title LIKE '%$safe_location%'
                OR properties.address LIKE '%$safe_location%'
              )";
}


/* =========================
   MINIMUM PRICE
========================= */

if ($min_price !== '' && is_numeric($min_price)) {

    $min_price_value = (float)$min_price;

    $sql .= " AND properties.price_per_night >= $min_price_value";
}


/* =========================
   MAXIMUM PRICE
========================= */

if ($max_price !== '' && is_numeric($max_price)) {

    $max_price_value = (float)$max_price;

    $sql .= " AND properties.price_per_night <= $max_price_value";
}


/* =========================
   BEDROOMS
========================= */

if ($bedrooms !== '' && is_numeric($bedrooms)) {

    $bedrooms_value = (int)$bedrooms;

    $sql .= " AND properties.bedrooms >= $bedrooms_value";
}


/* =========================
   GUESTS
========================= */

if ($guests !== '' && is_numeric($guests)) {

    $guests_value = (int)$guests;

    $sql .= " AND properties.max_guests >= $guests_value";
}


/* =========================
   PROPERTY TYPE
========================= */

$allowed_types = [
    'Apartment',
    'Studio',
    'Villa'
];

if (in_array($property_type, $allowed_types, true)) {

    $safe_type = $conn->real_escape_string($property_type);

    $sql .= " AND properties.property_type = '$safe_type'";
}


/* =========================
   AMENITIES FILTER
========================= */

$amenity_ids = [];

foreach ($selected_amenities as $amenity_id) {

    if (is_numeric($amenity_id)) {

        $amenity_ids[] = (int)$amenity_id;

    }
}


if (!empty($amenity_ids)) {

    $ids = implode(',', $amenity_ids);

    $required_count = count($amenity_ids);

    $sql .= "
        AND properties.id IN (

            SELECT property_id

            FROM property_amenities

            WHERE amenity_id IN ($ids)

            GROUP BY property_id

            HAVING COUNT(DISTINCT amenity_id) = $required_count

        )
    ";
}


/* =========================
   SORT
========================= */

if ($sort === 'low') {

    $sql .= "
        ORDER BY properties.price_per_night ASC
    ";

} elseif ($sort === 'high') {

    $sql .= "
        ORDER BY properties.price_per_night DESC
    ";

} else {

    $sql .= "
        ORDER BY properties.id DESC
    ";
}


/* =========================
   EXECUTE QUERY
========================= */

$result = $conn->query($sql);


/* =========================
   GET AMENITIES
========================= */

$amenities_query = "
    SELECT *
    FROM amenities
    ORDER BY name
";

$amenities_result = $conn->query($amenities_query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search Properties - StayEase</title>

    <link rel="stylesheet" href="../assets/css/properties.css">

</head>


<body>


<!-- ================= NAVBAR ================= -->

<?php require_once __DIR__ . "/../includes/navbar.php"; ?>



<!-- ================= SEARCH PAGE ================= -->

<main class="search-page">


    <h1>
        Find Your Perfect Stay
    </h1>

    <p class="search-subtitle">
        Search and filter properties based on your preferences.
    </p>



    <!-- ================= FILTER FORM ================= -->

    <form
        action="search.php"
        method="GET"
        class="filter-form"
    >


        <!-- LOCATION -->

        <div class="filter-group">

            <label>
                Where are you going?
            </label>

            <input
                type="text"
                name="location"
                placeholder="Cairo, Giza, Alexandria..."
                value="<?php echo htmlspecialchars($location); ?>"
            >

        </div>



        <!-- PRICE -->

        <div class="filter-row">


            <div class="filter-group">

                <label>
                    Minimum Price
                </label>

                <input
                    type="number"
                    name="min_price"
                    placeholder="Min price"
                    value="<?php echo htmlspecialchars($min_price); ?>"
                    min="0"
                >

            </div>



            <div class="filter-group">

                <label>
                    Maximum Price
                </label>

                <input
                    type="number"
                    name="max_price"
                    placeholder="Max price"
                    value="<?php echo htmlspecialchars($max_price); ?>"
                    min="0"
                >

            </div>


        </div>



        <!-- BEDROOMS -->

        <div class="filter-group">

            <label>
                Minimum Bedrooms
            </label>

            <select name="bedrooms">

                <option value="">
                    Any
                </option>

                <option
                    value="1"
                    <?php
                    if ($bedrooms == '1') {
                        echo 'selected';
                    }
                    ?>
                >
                    1+
                </option>

                <option
                    value="2"
                    <?php
                    if ($bedrooms == '2') {
                        echo 'selected';
                    }
                    ?>
                >
                    2+
                </option>

                <option
                    value="3"
                    <?php
                    if ($bedrooms == '3') {
                        echo 'selected';
                    }
                    ?>
                >
                    3+
                </option>

                <option
                    value="4"
                    <?php
                    if ($bedrooms == '4') {
                        echo 'selected';
                    }
                    ?>
                >
                    4+
                </option>

            </select>

        </div>



        <!-- GUESTS -->

        <div class="filter-group">

            <label>
                Guests
            </label>

            <select name="guests">

                <option value="">
                    Any
                </option>

                <option
                    value="2"
                    <?php
                    if ($guests == '2') {
                        echo 'selected';
                    }
                    ?>
                >
                    2+
                </option>

                <option
                    value="4"
                    <?php
                    if ($guests == '4') {
                        echo 'selected';
                    }
                    ?>
                >
                    4+
                </option>

                <option
                    value="6"
                    <?php
                    if ($guests == '6') {
                        echo 'selected';
                    }
                    ?>
                >
                    6+
                </option>

                <option
                    value="8"
                    <?php
                    if ($guests == '8') {
                        echo 'selected';
                    }
                    ?>
                >
                    8+
                </option>

            </select>

        </div>



        <!-- PROPERTY TYPE -->

        <div class="filter-group">

            <label>
                Property Type
            </label>

            <select name="property_type">

                <option value="">
                    All Types
                </option>

                <option
                    value="Apartment"
                    <?php
                    if ($property_type == 'Apartment') {
                        echo 'selected';
                    }
                    ?>
                >
                    Apartment
                </option>

                <option
                    value="Studio"
                    <?php
                    if ($property_type == 'Studio') {
                        echo 'selected';
                    }
                    ?>
                >
                    Studio
                </option>

                <option
                    value="Villa"
                    <?php
                    if ($property_type == 'Villa') {
                        echo 'selected';
                    }
                    ?>
                >
                    Villa
                </option>

            </select>

        </div>



        <!-- AMENITIES -->

        <div class="filter-group">

            <label>
                Amenities
            </label>


            <div class="amenities-checkboxes">

                <?php

                if ($amenities_result && $amenities_result->num_rows > 0) {

                    while ($amenity = $amenities_result->fetch_assoc()) {

                        $checked = in_array(
                            (string)$amenity['id'],
                            array_map('strval', $selected_amenities),
                            true
                        )
                        ? 'checked'
                        : '';

                ?>

                    <label class="checkbox-label">

                        <input
                            type="checkbox"
                            name="amenities[]"
                            value="<?php echo $amenity['id']; ?>"
                            <?php echo $checked; ?>
                        >

                        <?php
                        echo htmlspecialchars($amenity['name']);
                        ?>

                    </label>

                <?php

                    }

                } else {

                    echo "<p>No amenities available.</p>";

                }

                ?>

            </div>

        </div>



        <!-- SORT -->

        <div class="filter-group">

            <label>
                Sort By
            </label>

            <select name="sort">

                <option value="">
                    Recommended
                </option>

                <option
                    value="low"
                    <?php
                    if ($sort == 'low') {
                        echo 'selected';
                    }
                    ?>
                >
                    Price: Low to High
                </option>

                <option
                    value="high"
                    <?php
                    if ($sort == 'high') {
                        echo 'selected';
                    }
                    ?>
                >
                    Price: High to Low
                </option>

            </select>

        </div>



        <!-- BUTTONS -->

        <div class="filter-buttons">

            <button
                type="submit"
                class="search-btn"
            >
                Search Properties
            </button>


            <a
                href="search.php"
                class="clear-btn"
            >
                Clear Filters
            </a>

        </div>


    </form>



    <!-- ================= RESULTS ================= -->

    <section class="search-results">


        <h2>
            Available Properties
        </h2>


        <div class="properties-container">


            <?php

            if ($result && $result->num_rows > 0) {

                while ($property = $result->fetch_assoc()) {

                    $property_id = (int)$property['id'];

            ?>


                <!-- ================= PROPERTY CARD ================= -->

                <div class="property-card">


                    <!-- IMAGE -->

                    <div class="property-image">

                        <?php

                        $image_sql = "
                            SELECT image
                            FROM property_images
                            WHERE property_id = $property_id
                            LIMIT 1
                        ";

                        $image_result = $conn->query($image_sql);


                        if ($image_result && $image_result->num_rows > 0) {

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

                                No Image

                            </div>

                        <?php

                        }

                        ?>

                    </div>



                    <!-- PROPERTY INFO -->

                    <div class="property-info">


                        <span class="property-type">

                            <?php
                            echo htmlspecialchars(
                                $property['property_type']
                            );
                            ?>

                        </span>



                        <h2>

                            <?php
                            echo htmlspecialchars(
                                $property['title']
                            );
                            ?>

                        </h2>



                        <p class="location">

                            <?php
                            echo htmlspecialchars(
                                $property['location']
                            );
                            ?>

                        </p>



                        <!-- FEATURES -->

                        <div class="property-features">


                            <span>

                                <?php
                                echo $property['bedrooms'];
                                ?>

                                Bedrooms

                            </span>


                            <span>

                                <?php
                                echo $property['bathrooms'];
                                ?>

                                Bathrooms

                            </span>


                            <span>

                                <?php
                                echo $property['max_guests'];
                                ?>

                                Guests

                            </span>


                        </div>



                        <!-- PRICE + DETAILS -->

                        <div class="card-bottom">


                            <div class="price">

                                <strong>

                                    <?php
                                    echo $property['price_per_night'];
                                    ?>

                                    EGP

                                </strong>

                                <span>
                                    / night
                                </span>

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

            ?>


                <!-- NO RESULTS -->

                <div class="no-results">

                    <h3>
                        No properties found
                    </h3>

                    <p>
                        Try changing your search or filters.
                    </p>

                </div>


            <?php

            }

            ?>


        </div>


    </section>


</main>


</body>

</html>