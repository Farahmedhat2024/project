<?php

require_once "../config/database.php";

if (isset($_GET['id']) && isset($_GET['status'])) {

    $id = $_GET['id'];
    $status = $_GET['status'];

    mysqli_query(
        $conn,
        "UPDATE bookings SET status = '$status' WHERE id = $id"
    );

}

header("Location: bookings.php");
exit;

?>