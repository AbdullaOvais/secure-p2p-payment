<?php
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf_token();
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $email === "" || $password === "") {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
         "INSERT INTO users (username, email, password_hash)
            VALUES (?, ?, ?)" 
            );

          $stmt->bind_param("sss", $username, $email, $password_hash);

          try {
 
          $stmt->execute();
   
          $message = "Registration successful. You can now login.";
 
            } catch (mysqli_sql_exception $e) {

             if ($e->getCode() === 1062) {
               $message = "Username or email already exists.";
                 } else {
                 $message = "Registration failed.";
           }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Secure P2P Payment</title>
</head>

<body>

    <h1>Create Account</h1>

    <?php if ($message !== ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input
        type="hidden"
        name="csrf_token"
        value="<?php echo htmlspecialchars(csrf_token()); ?>"
        >
        <label>Username:</label><br>
        <input type="text" name="username" required>
        <br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required minlength="8">
        <br><br>

        <button type="submit">Register</button>

    </form>

    <br>

    <a href="index.php">Back to Home</a>

</body>
</html>
