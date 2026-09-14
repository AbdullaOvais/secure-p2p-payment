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

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT action, ip_address, created_at
     FROM activity_logs
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Activity Logs - Secure P2P Payment</title>
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
            </div>

        </div>

    </div>

    <div class="container">

        <div class="card">

            <h1>Activity Logs</h1>

            <p>
                Review your recent account and security activity.
            </p>

            <?php if ($result->num_rows > 0): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php while ($row = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($row["action"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row["ip_address"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row["created_at"]); ?>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="message">
                    No activity logs found.
                </div>

            <?php endif; ?>

        </div>

        <div style="text-align: center;">
            <a href="dashboard.php" class="btn">
                Back to Dashboard
            </a>
        </div>

    </div>

</body>

</html>

<?php

$stmt->close();

?>
