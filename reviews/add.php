<?php

require_once "../config/database.php";

if (isset($_POST['add_review'])) {

    $user_id = $_POST['user_id'];
    $property_id = $_POST['property_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $result = mysqli_query($conn, "SELECT MAX(id) AS max_id FROM reviews");

    $row = mysqli_fetch_assoc($result);

    $next_id = $row['max_id'] + 1;

    $sql = "INSERT INTO reviews
    (id, user_id, property_id, rating, comment, created_at)
    VALUES
    ('$next_id', '$user_id', '$property_id', '$rating', '$comment', CURDATE())";

    mysqli_query($conn, $sql);

    echo "Review added successfully!";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Review</title>

</head>

<body>

    <h1>Add Review</h1>

    <form method="POST">

        <label>User ID:</label>
        <input type="number" name="user_id" required>

        <br><br>

        <label>Property ID:</label>
        <input type="number" name="property_id" required>

        <br><br>

        <label>Rating:</label>

        <select name="rating" required>

            <option value="">Choose Rating</option>
            <option value="1">1 Star</option>
            <option value="2">2 Stars</option>
            <option value="3">3 Stars</option>
            <option value="4">4 Stars</option>
            <option value="5">5 Stars</option>

        </select>

        <br><br>

        <label>Comment:</label>

        <br>

        <textarea name="comment" rows="5" required></textarea>

        <br><br>

        <button type="submit" name="add_review">
            Add Review
        </button>

    </form>

</body>

</html>