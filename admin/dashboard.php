<?php

session_start();

require_once "../config/database.php";

// Count Users
$result = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$row = mysqli_fetch_assoc($result);
$total_users = $row['total_users'];

// Count Properties
$result = mysqli_query($conn, "SELECT COUNT(*) AS total_properties FROM properties");
$row = mysqli_fetch_assoc($result);
$total_properties = $row['total_properties'];

// Count Bookings
$result = mysqli_query($conn, "SELECT COUNT(*) AS total_bookings FROM bookings");
$row = mysqli_fetch_assoc($result);
$total_bookings = $row['total_bookings'];

// Count Reviews
$result = mysqli_query($conn, "SELECT COUNT(*) AS total_reviews FROM reviews");
$row = mysqli_fetch_assoc($result);
$total_reviews = $row['total_reviews'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StayEase - Admin Dashboard</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            background-color: #f5f5f5;
        }

        h1 {
            margin-bottom: 30px;
        }

        .cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            width: 200px;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            margin-bottom: 15px;
        }

        .number {
            font-size: 35px;
            font-weight: bold;
        }

    </style>

</head>

<body>

    <h1>StayEase Admin Dashboard</h1>

    <div class="cards">

        <div class="card">
            <h2>Users</h2>
            <p class="number"><?php echo $total_users; ?></p>
        </div>

        <div class="card">
            <h2>Properties</h2>
            <p class="number"><?php echo $total_properties; ?></p>
        </div>

        <div class="card">
            <h2>Bookings</h2>
            <p class="number"><?php echo $total_bookings; ?></p>
        </div>

        <div class="card">
            <h2>Reviews</h2>
            <p class="number"><?php echo $total_reviews; ?></p>
        </div>

    </div>

</body>

</html>
