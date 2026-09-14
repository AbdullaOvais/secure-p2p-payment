<?php

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

require_once __DIR__ . '/../config/security.php';

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
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="navbar">

        <div class="nav-inner">

            <h2>Secure P2P Payment</h2>

            <div>
                <a href="dashboard.php">Dashboard</a>
                <a href="send_money.php">Send Money</a>
                <a href="transactions.php">Transactions</a>
                <a href="activity_logs.php">Activity Logs</a>

                <form method="POST" action="logout.php" style="display: inline;">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars(csrf_token()); ?>"
                    >

                    <button type="submit" class="logout-btn">
                        Logout
                    </button>
                </form>
            </div>

        </div>

    </div>

    <div class="container">

        <div class="card">

            <h1>Welcome, <?php echo htmlspecialchars($user["username"]); ?>!</h1>

            <p>
                Manage your account and peer-to-peer payments securely.
            </p>

        </div>

        <div class="balance-card">

            <p>Available Balance</p>

            <div class="balance">
                ₹<?php echo htmlspecialchars($user["balance"]); ?>
            </div>

        </div>

        <div class="dashboard-grid">

            <div class="dashboard-card">

                <h3>Send Money</h3>

                <p>
                    Transfer money securely to another user.
                </p>

                <a href="send_money.php" class="btn">
                    Send Money
                </a>

            </div>

            <div class="dashboard-card">

                <h3>Transactions</h3>

                <p>
                    View your payment transaction history.
                </p>

                <a href="transactions.php" class="btn">
                    View Transactions
                </a>

            </div>

            <div class="dashboard-card">

                <h3>Activity Logs</h3>

                <p>
                    View your account security activity.
                </p>

                <a href="activity_logs.php" class="btn">
                    View Activity
                </a>

            </div>

        </div>

        <div class="card">

            <h3>Account Information</h3>

            <p>
                <strong>Username:</strong>
                <?php echo htmlspecialchars($user["username"]); ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?php echo htmlspecialchars($user["email"]); ?>
            </p>

        </div>

    </div>

</body>

</html>
   
