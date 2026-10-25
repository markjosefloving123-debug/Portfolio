<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "portfolio_system";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Unable to connect to the database. Please try again later.");
}

$conn->set_charset("utf8mb4");

?>

