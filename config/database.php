<?php

$host = getenv("DB_HOST") ?: "db";
$dbname = getenv("MYSQL_DATABASE") ?: "p2p_payment";
$username = getenv("MYSQL_USER") ?: "p2p_user";
$password = getenv("MYSQL_PASSWORD") ?: "p2p_password";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");
?>
