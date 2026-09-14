<?php
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
$message = "";
$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_csrf_token();
    $receiver_username = trim($_POST["receiver_username"] ?? "");
    $amount = trim($_POST["amount"] ?? "");

    if ($receiver_username === "" || $amount === "") {

    $message = "All fields are required.";

    } elseif (strlen($receiver_username) < 3 || strlen($receiver_username) > 50) {

    $message = "Invalid receiver username.";

    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $receiver_username)) {

    $message = "Invalid receiver username.";

    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {

    $message = "Please enter a valid amount.";

    } elseif ((float)$amount <= 0) {

    $message = "Amount must be greater than zero.";

    } elseif ((float)$amount > 1000000) {

    $message = "Amount exceeds the maximum allowed limit.";

    } else {

        $amount = round((float)$amount, 2);

        $conn->begin_transaction();

        try {

            // Get sender
            $stmt = $conn->prepare(
                "SELECT id, balance
                 FROM users
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $sender_result = $stmt->get_result();
            $sender = $sender_result->fetch_assoc();

            $stmt->close();

            if (!$sender) {
                throw new Exception("Sender account not found.");
            }

            // Get receiver
            $stmt = $conn->prepare(
                "SELECT id
                 FROM users
                 WHERE username = ?
                 FOR UPDATE"
            );

            $stmt->bind_param("s", $receiver_username);
            $stmt->execute();

            $receiver_result = $stmt->get_result();
            $receiver = $receiver_result->fetch_assoc();

            $stmt->close();

            if (!$receiver) {
                throw new Exception("Receiver not found.");
            }

            if ((int)$receiver["id"] === (int)$user_id) {
                throw new Exception("You cannot send money to yourself.");
            }

            // Check balance
            if ((float)$sender["balance"] < $amount) {
                throw new Exception("Insufficient balance.");
            }

            // Deduct from sender
            $stmt = $conn->prepare(
                "UPDATE users
                 SET balance = balance - ?
                 WHERE id = ?"
            );

            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();
            $stmt->close();

            // Add to receiver
            $stmt = $conn->prepare(
                "UPDATE users
                 SET balance = balance + ?
                 WHERE id = ?"
            );

            $stmt->bind_param("di", $amount, $receiver["id"]);
            $stmt->execute();
            $stmt->close();

            // Record transaction
            $status = "SUCCESS";

            $stmt = $conn->prepare(
                "INSERT INTO transactions
                 (sender_id, receiver_id, amount, status)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iids",
                $user_id,
                $receiver["id"],
                $amount,
                $status
            );

            $stmt->execute();
            $stmt->close();

            // Log sender activity
            $action = "Sent ₹" . number_format($amount, 2) .
                      " to " . $receiver_username;

            $ip_address = $_SERVER["REMOTE_ADDR"] ?? "";

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

            $conn->commit();

            $message = "Payment successful! ₹" .
                       number_format($amount, 2) .
                       " sent to " .
                       htmlspecialchars($receiver_username) .
                       ".";

        } catch (Exception $e) {

            $conn->rollback();

            $message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Money - Secure P2P Payment</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <h1>Send Money</h1>

    <?php if ($message !== ""): ?>
        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input
        type="hidden"
        name="csrf_token"
        value="<?php echo htmlspecialchars(csrf_token()); ?>"
        >
        <label>Receiver Username:</label><br>

        <input
            type="text"
            name="receiver_username"
            required
        >

        <br><br>

        <label>Amount:</label><br>

        <input
            type="number"
            name="amount"
            min="0.01"
            step="0.01"
            required
        >

        <br><br>

        <button type="submit">
            Send Money
        </button>

    </form>

    <br>

    <a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
