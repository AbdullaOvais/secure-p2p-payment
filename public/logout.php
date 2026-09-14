<?php

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require_csrf_token();

$user_id = $_SESSION["user_id"];
$ip_address = $_SERVER["REMOTE_ADDR"] ?? "";

$action = "LOGOUT";

$stmt = $conn->prepare(
    "INSERT INTO activity_logs
     (user_id, action, ip_address)
     VALUES (?, ?, ?)"
);

$stmt->bind_param(
    "iss",
    $user_id,
    $action,
    $ip_address
);

$stmt->execute();
$stmt->close();

$_SESSION = [];

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: index.php");
exit;
