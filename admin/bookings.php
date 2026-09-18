<?php

require_once "../config/database.php";

$result = mysqli_query($conn, "SELECT * FROM bookings");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Bookings</title>
    <style>
    a:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }
</style>

</head>

<body>

    <h1>Manage Bookings</h1>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Property ID</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Guests</th>
            <th>Total Price</th>
            <th>Status</th>
            <th>Action</th>
            <th>Created At</th>
        </tr>

        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>

            <tr>

                <td><?php echo $booking['id']; ?></td>

                <td><?php echo $booking['user_id']; ?></td>

                <td><?php echo $booking['property_id']; ?></td>

                <td><?php echo $booking['check_in']; ?></td>

                <td><?php echo $booking['check_out']; ?></td>

                <td><?php echo $booking['guests']; ?></td>

                <td><?php echo $booking['total_price']; ?></td>

                <td><?php echo $booking['status']; ?></td>

                <td>

                    <a href="update-booking.php?id=<?php echo $booking['id']; ?>&status=confirmed"
                       style="background-color: #198754; color: white; padding: 5px 8px; text-decoration: none; border-radius: 4px; font-size: 12px;display: inline-block;">
                        Confirm
                    </a>

                    <a href="update-booking.php?id=<?php echo $booking['id']; ?>&status=cancelled"
                       style="background-color: #dc3545; color: white; padding: 5px 8px; text-decoration: none; border-radius: 4px; font-size: 12px; display: inline-block;">
                        Cancel
                    </a>

                </td>

                <td><?php echo $booking['created_at']; ?></td>

            </tr>

        <?php } ?>

    </table>

</body>

</html>