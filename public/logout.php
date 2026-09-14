<?php
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);
session_start();

$_SESSION = [];

session_destroy();

header("Location: index.php");
exit;
