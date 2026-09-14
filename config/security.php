<?php

function csrf_token()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function verify_csrf_token($token)
{
    return isset($_SESSION["csrf_token"])
        && is_string($token)
        && hash_equals($_SESSION["csrf_token"], $token);
}

function require_csrf_token()
{
    $token = $_POST["csrf_token"] ?? "";

    if (!verify_csrf_token($token)) {
        http_response_code(403);
        die("Invalid security token.");
    }
}
