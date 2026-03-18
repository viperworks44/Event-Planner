<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark all unread notifications as read
$update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();

// Fetch notifications
$stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Your Notifications</title>    
    </head>
    <header>
        <h2>Event Planner Notifications</h2>
        <style>
            body {
                font-family: Arial, sans-serif;
            }
            .notification {
                border-bottom: 1px solid #ccc;
                padding: 10px;
                width: 97%;
            }
            .timestamp {
                font-size: 0.85em;
                color: gray;
            }
        </style>
    </header>
    <body>
        <br>
        <h2>Notifications</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="notification">
                    <div><?= htmlspecialchars($row['message']) ?></div>
                    <div class="timestamp"><?= htmlspecialchars(date("F j, Y, g:i a", strtotime($row['created_at']))) ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No notifications.</p>
        <?php endif; ?>

        <br>
        <a href="../events/list.php">← Back to Event List</a><br>
    </body>
    <style>
        html,body{
            display:flex;
            flex-direction:column;
            align-items: center;
            justify-content:stretch;
            height: 100%;
            width: 100%;
        }
            
        header {
            background-color: #4faaff;
            padding: 10px;
            text-align: center;
            position: fixed;
            top:0;
            width: 100%;
        }

        main {
            flex: 1;
            display: flex;
            margin-top: 100px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 100px;
        }

        footer {
            margin-top: auto;
            text-align: center;
            padding-top: 15px;
            padding-bottom: 15px;
            background-color: #4faaff;
            bottom: 0;
            width: 100%;
            margin-bottom: -8px;
        }
    </style>
    <footer>
        <p>&copy; 2025 Event Planner</p>
    </footer>
</html>
