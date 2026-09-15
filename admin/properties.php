`php
<?php

require_once "../config/database.php";


// =========================
// Add New Property
// =========================

if (isset($_POST['add_property'])) {

    $titel = $_POST['titel'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $address = $_POST['address'];
    $price_per_night = $_POST['price_per_night'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $max_guests = $_POST['max_guests'];
    $property_type = $_POST['property_type'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];


    // Get next ID
    $result = mysqli_query($conn, "SELECT MAX(id) AS max_id FROM properties");
    $row = mysqli_fetch_assoc($result);

    $new_id = ($row['max_id'] ?? 0) + 1;


    // Insert property using Prepared Statement
    $sql = "INSERT INTO properties
    (
        id,
        titel,
        description,
        location,
        address,
        price_per_night,
        bedrooms,
        bathrooms,
        max_guests,
        property_type,
        latitude,
        longitude
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "issssiiissdd",
        $new_id,
        $titel,
        $description,
        $location,
        $address,
        $price_per_night,
        $bedrooms,
        $bathrooms,
        $max_guests,
        $property_type,
        $latitude,
        $longitude
    );


    if (mysqli_stmt_execute($stmt)) {

        echo "<p style='color: green;'>Property added successfully.</p>";

    } else {

        echo "<p style='color: red;'>Error adding property: "
            . mysqli_error($conn)
            . "</p>";
    }

    mysqli_stmt_close($stmt);
}


// =========================
// Get All Properties
// =========================

$result = mysqli_query($conn, "SELECT * FROM properties");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Properties</title>

</head>

<body>

    <h1>Manage Properties</h1>


    <!-- =========================
         Add Property Form
    ========================== -->

    <h2>Add New Property</h2>

    <form method="POST">

        <label>Title:</label>

        <input
            type="text"
            name="titel"
            required
        >

        <br><br>


        <label>Description:</label>

        <textarea
            name="description"
            required
        ></textarea>

        <br><br>


        <label>Location:</label>

        <input
            type="text"
            name="location"
            required
        >

        <br><br>


        <label>Address:</label>

        <input
            type="text"
            name="address"
        >

        <br><br>


        <label>Price Per Night:</label>

        <input
            type="number"
            name="price_per_night"
            min="0"
            required
        >

        <br><br>


        <label>Bedrooms:</label>

        <input
            type="number"
            name="bedrooms"
            min="0"
            required
        >

        <br><br>


        <label>Bathrooms:</label>

        <input
            type="number"
            name="bathrooms"
            min="0"
            required
        >

        <br><br>


        <label>Max Guests:</label>

        <input
            type="number"
            name="max_guests"
            min="1"
            required
        >

        <br><br>


        <label>Property Type:</label>

        <select name="property_type">

            <option value="apartment">Apartment</option>

            <option value="villa">Villa</option>

            <option value="chalet">Chalet</option>

            <option value="studio">Studio</option>

            <option value="house">House</option>

        </select>

        <br><br>


        <label>Latitude:</label>
        <input
            type="text"
            name="latitude"
        >

        <br><br>


        <label>Longitude:</label>

        <input
            type="text"
            name="longitude"
        >

        <br><br>


        <button
            type="submit"
            name="add_property"
        >
            Add Property
        </button>

    </form>


    <hr>


    <!-- =========================
         Properties Table
    ========================== -->

    <h2>All Properties</h2>

    <table border="1" cellpadding="10">

        <tr>

            <th>ID</th>

            <th>Title</th>

            <th>Location</th>

            <th>Price Per Night</th>

            <th>Bedrooms</th>

            <th>Max Guests</th>

            <th>Type</th>

            <th>Action</th>

        </tr>


        <?php while ($property = mysqli_fetch_assoc($result)) { ?>

            <tr>

                <td>
                    <?php echo htmlspecialchars($property['id']); ?>
                </td>


                <td>
                    <?php echo htmlspecialchars($property['titel']); ?>
                </td>


                <td>
                    <?php echo htmlspecialchars($property['location']); ?>
                </td>


                <td>
                    <?php echo htmlspecialchars($property['price_per_night']); ?>
                </td>


                <td>
                    <?php echo htmlspecialchars($property['bedrooms']); ?>
                </td>


                <td>
                    <?php echo htmlspecialchars($property['max_guests']); ?>
                </td>


                <td>
                    <?php echo htmlspecialchars($property['property_type']); ?>
                </td>


               

   <td>

    <div style="display: flex; gap: 5px; align-items: center;">

        <a
            href="edit-property.php?id=<?php echo $property['id']; ?>"
            style="
                padding: 5px 9px;
                background-color: #0d6efd;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
            "
        >
            Edit
        </a>

        <a
            href="delete-property.php?id=<?php echo $property['id']; ?>"
            onclick="return confirm('Are you sure you want to delete this property?');"
            style="
                padding: 5px 9px;
                background-color: #dc3545;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
            "
        >
            Delete
        </a>

    </div>

</td>

            </tr>

        <?php } ?>

    </table>

</body>

</html>
`