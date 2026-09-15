<?php

require_once "../config/database.php";

$result = mysqli_query($conn, "SELECT * FROM users");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users</title>

</head>

<body>

    <h1>Manage Users</h1>

    <table border="1" cellpadding="10">

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($user = mysqli_fetch_assoc($result)) { ?>

            <tr>

                <td><?php echo $user['id']; ?></td>

                <td><?php echo $user['name']; ?></td>

                <td><?php echo $user['email']; ?></td>

                <td><?php echo $user['phone']; ?></td>

                <td><?php echo $user['role']; ?></td>

                <td><?php echo $user['created_at']; ?></td>

                <td><?php echo $user['status']; ?></td>

                <td>
    <a href="deactivate-user.php?id=<?php echo $user['id']; ?>"
       onclick="return confirm('Are you sure you want to deactivate this user?');"
       style="background-color: #dc3545; color: white; padding: 5px 8px; text-decoration: none; border-radius: 4px; font-size: 12px;">
        Deactivate
    </a>
</td>

            </tr>

        <?php } ?>

    </table>

</body>

</html>