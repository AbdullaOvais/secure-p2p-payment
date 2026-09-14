<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $ip_address = $_SERVER["REMOTE_ADDR"] ?? "";

    if ($username === "" || $password === "") {

        $message = "Username and password are required.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, password_hash
             FROM users
             WHERE username = ?"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password_hash"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];

                // Record successful login
                $action = "LOGIN_SUCCESS";

                $log_stmt = $conn->prepare(
                    "INSERT INTO activity_logs
                     (user_id, action, ip_address)
                     VALUES (?, ?, ?)"
                );

                $log_stmt->bind_param(
                    "iss",
                    $user["id"],
                    $action,
                    $ip_address
                );

                $log_stmt->execute();
                $log_stmt->close();

                $stmt->close();

                header("Location: dashboard.php");
                exit;

            } else {

                // Record failed login for existing user
                $action = "LOGIN_FAILED";

                $log_stmt = $conn->prepare(
                    "INSERT INTO activity_logs
                     (user_id, action, ip_address)
                     VALUES (?, ?, ?)"
                );

                $log_stmt->bind_param(
                    "iss",
                    $user["id"],
                    $action,
                    $ip_address
                );

                $log_stmt->execute();
                $log_stmt->close();

                $message = "Invalid username or password.";
            }

        } else {

            $message = "Invalid username or password.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - Secure P2P Payment</title>
</head>

<body>

    <h1>Login</h1>

    <?php if ($message !== ""): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Username:</label><br>

        <input
            type="text"
            name="username"
            required
        >

        <br><br>

        <label>Password:</label><br>

        <input
            type="password"
            name="password"
            required
        >

        <br><br>

        <button type="submit">
            Login
        </button>

    </form>

    <br>

    <a href="register.php">Create Account</a>

    <br>

    <a href="index.php">Back to Home</a>

</body>

</html>
