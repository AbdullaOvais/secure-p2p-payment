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

        } elseif (strlen($username) < 3 || strlen($username) > 50) {

         $message = "Username must be between 3 and 50 characters.";

        } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {

           $message = "Username can contain only letters, numbers, and underscores.";

       } elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

         $message = "Please enter a valid email address.";

       } elseif (strlen($password) < 8 || strlen($password) > 72) {

         $message = "Password must be between 8 and 72 characters.";
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
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="auth-container">

        <div class="card">

            <h1>Create Account</h1>

            <?php if ($message !== ""): ?>

                <div class="message">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(csrf_token()); ?>"
                >

                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                >

                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

                <button type="submit">
                    Create Account
                </button>

            </form>

            <div class="auth-footer">

                <a href="login.php">Already have an account? Login</a>

                <br><br>

                <a href="index.php">Back to Home</a>

            </div>

        </div>

    </div>

</body>

</html>
