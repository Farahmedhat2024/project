`php
<?php

require_once "../config/database.php";

$id = $_GET['id'];


// Get property data
$result = mysqli_query($conn, "SELECT * FROM properties WHERE id = $id");

$property = mysqli_fetch_assoc($result);


// Update property
if (isset($_POST['update_property'])) {

    $titel = $_POST['titel'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $address = $_POST['address'];
    $price_per_night = $_POST['price_per_night'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $max_guests = $_POST['max_guests'];
    $property_type = $_POST['property_type'];


    $sql = "UPDATE properties SET
            titel = '$titel',
            description = '$description',
            location = '$location',
            address = '$address',
            price_per_night = '$price_per_night',
            bedrooms = '$bedrooms',
            bathrooms = '$bathrooms',
            max_guests = '$max_guests',
            property_type = '$property_type'
            WHERE id = $id";


    mysqli_query($conn, $sql);


    header("Location: properties.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Property</title>

</head>


<body>

    <h1>Edit Property</h1>


    <form method="POST">


        <label>Title:</label>

        <input
            type="text"
            name="titel"
            value="<?php echo $property['titel']; ?>"
            required
        >

        <br><br>


        <label>Description:</label>

        <textarea
            name="description"
            required
        ><?php echo $property['description']; ?></textarea>

        <br><br>


        <label>Location:</label>

        <input
            type="text"
            name="location"
            value="<?php echo $property['location']; ?>"
            required
        >

        <br><br>


        <label>Address:</label>

        <input
            type="text"
            name="address"
            value="<?php echo $property['address']; ?>"
        >

        <br><br>


        <label>Price Per Night:</label>

        <input
            type="number"
            name="price_per_night"
            value="<?php echo $property['price_per_night']; ?>"
            required
        >

        <br><br>


        <label>Bedrooms:</label>

        <input
            type="number"
            name="bedrooms"
            value="<?php echo $property['bedrooms']; ?>"
            required
        >

        <br><br>


        <label>Bathrooms:</label>

        <input
            type="number"
            name="bathrooms"
            value="<?php echo $property['bathrooms']; ?>"
            required
        >

        <br><br>


        <label>Max Guests:</label>

        <input
            type="number"
            name="max_guests"
            value="<?php echo $property['max_guests']; ?>"
            required
        >

        <br><br>


        <label>Property Type:</label>

        <select name="property_type">

            <option value="apartment"
                <?php if ($property['property_type'] == 'apartment') echo 'selected'; ?>>
                Apartment
            </option>

            <option value="villa"
                <?php if ($property['property_type'] == 'villa') echo 'selected'; ?>>
                Villa
            </option>

            <option value="chalet"
                <?php if ($property['property_type'] == 'chalet') echo 'selected'; ?>>
                Chalet
            </option>

            <option value="studio"
                <?php if ($property['property_type'] == 'studio') echo 'selected'; ?>>
                Studio
            </option>

            <option value="house"
                <?php if ($property['property_type'] == 'house') echo 'selected'; ?>>
                House
            </option>

        </select>

        <br><br>
        <button type="submit" name="update_property">
            Update Property
        </button>


    </form>


</body>

</html>
`