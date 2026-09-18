<?php

require_once "../config/database.php";

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    mysqli_query($conn, "UPDATE users SET status = 'inactive' WHERE id = $id");

}

header("Location: users.php");
exit;

?>