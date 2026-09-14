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
    "SELECT
        t.id,
        t.sender_id,
        s.username AS sender,
        r.username AS receiver,
        t.amount,
        t.status,
        t.created_at
     FROM transactions t
     INNER JOIN users s ON t.sender_id = s.id
     INNER JOIN users r ON t.receiver_id = r.id
     WHERE t.sender_id = ? OR t.receiver_id = ?
     ORDER BY t.created_at DESC"
);

$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Transaction History - Secure P2P Payment</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="navbar">

        <div class="nav-inner">

            <h2>Secure P2P Payment</h2>

            <div>
                <a href="dashboard.php">Dashboard</a>
                <a href="send_money.php">Send Money</a>
                <a href="activity_logs.php">Activity Logs</a>
            </div>

        </div>

    </div>

    <div class="container">

        <div class="card">

            <h1>Transaction History</h1>

            <p>
                View your recent payment transactions.
            </p>

            <?php if ($result->num_rows > 0): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php while ($row = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($row["created_at"]); ?>
                                    </td>

                                    <td>
                                        <?php
                                        if ((int)$row["sender_id"] === (int)$user_id) {
                                            echo "Sent";
                                        } else {
                                            echo "Received";
                                        }
                                        ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row["sender"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row["receiver"]); ?>
                                    </td>

                                    <td>
                                        ₹<?php echo number_format((float)$row["amount"], 2); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row["status"]); ?>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="message">
                    No transactions found.
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
