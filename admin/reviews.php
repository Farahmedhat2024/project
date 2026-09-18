<?php

require_once "../config/database.php";

$result = mysqli_query($conn, "SELECT * FROM reviews");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Reviews</title>

    <style>

        a {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 5px 9px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }

        a:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

    </style>

</head>

<body>

    <h1>Manage Reviews</h1>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Property ID</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>

        <?php while ($review = mysqli_fetch_assoc($result)) { ?>

            <tr>

                <td><?php echo $review['id']; ?></td>

                <td><?php echo $review['user_id']; ?></td>

                <td><?php echo $review['property_id']; ?></td>

                <td><?php echo $review['rating']; ?></td>

                <td><?php echo $review['comment']; ?></td>

                <td><?php echo $review['created_at']; ?></td>

                <td>

                    <a href="../reviews/delete.php?id=<?php echo $review['id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this review?');">
                        Delete
                    </a>

                </td>

            </tr>

        <?php } ?>

    </table>

</body>

</html>