<?php

$host = "db";
$dbname = "p2p_payment";
$username = "p2p_user";
$password = "p2p_password";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");
?>
