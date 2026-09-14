<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT username, email, balance
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Secure P2P Payment</title>
</head>

<body>

    <h1>Welcome, <?php echo htmlspecialchars($user["username"]); ?>!</h1>

    <h2>Account Details</h2>

    <p>
        <strong>Email:</strong>
        <?php echo htmlspecialchars($user["email"]); ?>
    </p>

    <p>
        <strong>Balance:</strong>
        ₹<?php echo number_format($user["balance"], 2); ?>
    </p>

    <hr>

    <h2>Payment</h2>

    <p>
    <a href="send_money.php">Send Money</a>
    </p>
   <p>
    <a href="transactions.php">Transaction History</a>
   </p>
   <p>
    <a href="activity_logs.php">Activity Logs</a>
   </p>

    <br>

    <a href="logout.php">Logout</a>

</body>
</html>
