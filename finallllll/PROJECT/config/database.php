<?php
declare(strict_types=1);

$host = 'localhost';
// $port = 3307;
$username = 'root';
$password = '';
$database = 'stayease';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
