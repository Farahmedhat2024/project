<?php



 

 

$conn = mysqli_connect('localhost', 'root', "", 'airbnb_clone');
 

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}
 

mysqli_set_charset($conn, "utf8mb4");
 