<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$current_user = $_SESSION['user_id'];
$message = '';

// Accept/Reject Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($request_id && in_array($action, ['accept', 'reject'])) {
        if ($action === 'accept') {
            // Accept the request
            $stmt = $conn->prepare("UPDATE friendships SET status = 'accepted' WHERE id = ? AND friend_id = ?");
            $stmt->bind_param("ii", $request_id, $current_user);
            $stmt->execute();

            // Get the user_id of the sender (the one who sent the request)
            $stmt = $conn->prepare("SELECT user_id FROM friendships WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $sender_id = $row['user_id'] ?? null;

            // Get current user's name (optional for message)
            $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->bind_param("i", $current_user);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $current_user_name = $user['name'] ?? 'Someone';

            // Insert notification for the sender
            if ($sender_id) {
                $note = "$current_user_name accepted your friend request.";
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmt->bind_param("is", $sender_id, $note);
                $stmt->execute();
            }

            $message = "Friend request accepted.";
        } else {
            // Reject the request
            $stmt = $conn->prepare("DELETE FROM friendships WHERE id = ? AND friend_id = ?");
            $stmt->bind_param("ii", $request_id, $current_user);
            $stmt->execute();
            $message = "Friend request rejected.";
        }
    }
}

// Fetch incoming requests
$stmt = $conn->prepare("
    SELECT friendships.id, users.name AS sender_name
    FROM friendships
    JOIN users ON friendships.user_id = users.id
    WHERE friendships.friend_id = ? AND friendships.status = 'pending'
");
$stmt->bind_param("i", $current_user);
$stmt->execute();
$requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Pending Friend Requests</title>
    </head>
    <body>
        <header>
            <h2>Pending Requests</h2>
        </header>
        <main>
            <?php if ($message): ?>
            <p><strong><?= htmlspecialchars($message) ?></strong></p>
            <?php endif; ?>

            <?php if ($requests->num_rows > 0): ?>
                <ul>
                <?php while ($row = $requests->fetch_assoc()): ?>
                    <li>
                        <?= htmlspecialchars($row['sender_name']) ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                            <button name="action" value="accept">Accept</button>
                            <button name="action" value="reject">Reject</button>
                        </form>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No pending requests.</p>
            <?php endif; ?>

            <br><a href="../friends/display_friends.php">← Back to Friends List</a>
        </main>
        <footer>
            <p>&copy; 2025 Event Planner</p>
        </footer>       
    </body>
    <style>
        html, body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: stretch;
            height: 100%;
            width: 100%;
        }

        header {
            background-color: #4faaff;
            padding: 10px;
            text-align: center;
            position: fixed;
            top: 0;
            width: 100%;
        }

        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 80px;
            margin-bottom: 80px;
        }

        footer {
            margin-top: auto;
            text-align: center;
            padding: 15px;
            background-color: #4faaff;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</html>
