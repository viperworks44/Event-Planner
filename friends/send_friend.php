<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$current_user = $_SESSION['user_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    // Lookup user by name (NOT email)
    $sql = "SELECT id FROM users WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $target_user = $row['id'];

        // Prevent sending request to yourself
        if ($target_user == $current_user) {
            $message = "You can't send a friend request to yourself.";
        } else {
            // Check if request or friendship already exists
            $sql = "SELECT * FROM friendships 
                    WHERE (user_id = ? AND friend_id = ?) 
                       OR (user_id = ? AND friend_id = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $current_user, $target_user, $target_user, $current_user);
            $stmt->execute();
            $check = $stmt->get_result();

            if ($check->num_rows > 0) {
                $message = "You are already friends or a request is pending.";
            } else {
                // Send friend request
                $sql = "INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'pending')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $current_user, $target_user);

                if ($stmt->execute()) {
                    $message = "Friend request sent to <strong>" . htmlspecialchars($username) . "</strong>!";
                } else {
                    $message = "Error sending request.";
                }
            }
        }
    } else {
        $message = "No user found with that name.";
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Send Friend Request</title>
    </head>
    <body>
        <header>
            <h2>Send a Friend Request</h2>
        </header>
        <main>
            <?php if ($message): ?>
                <p><?= $message ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="username">Enter name of user:</label><br>
                <input type="text" id="username" name="username" required>
                <button type="submit">Send Request</button>
            </form>

            <br>
            <a href="../friends/display_friends.php">← Back to Friends List</a>
        </main>
        <footer>
            <p>&copy; 2025 Event Planner</p>
        </footer> 
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
    </body>
</html>
