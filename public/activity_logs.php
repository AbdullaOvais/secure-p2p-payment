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

    <h1>Activity Logs</h1>

    <?php if ($result->num_rows > 0): ?>

        <table border="1" cellpadding="10" cellspacing="0">

            <tr>
                <th>Date & Time</th>
                <th>Activity</th>
                <th>IP Address</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($row["created_at"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["action"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["ip_address"]); ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <p>No activity logs found.</p>

    <?php endif; ?>

    <br>

    <a href="dashboard.php">Back to Dashboard</a>

</body>

</html>

<?php

$stmt->close();

?>
