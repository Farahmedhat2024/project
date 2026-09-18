<?php

require_once "../config/database.php";

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");

}

header("Location: ../admin/reviews.php");
exit;

?>



