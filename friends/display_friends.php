<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$current_user = $_SESSION['user_id'];

// Handle unfriend action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unfriend_id'])) {
    $unfriend_id = intval($_POST['unfriend_id']);

    $stmt = $conn->prepare("
        DELETE FROM friendships
        WHERE (user_id = ? AND friend_id = ?)
           OR (user_id = ? AND friend_id = ?)
    ");
    $stmt->bind_param("iiii", $current_user, $unfriend_id, $unfriend_id, $current_user);
    $stmt->execute();

    header("Location: display_friends.php");
    exit();
}

// Update user's last active time
$now = date("Y-m-d H:i:s");
$stmt = $conn->prepare("UPDATE users SET last_active = ? WHERE id = ?");
$stmt->bind_param("si", $now, $current_user);
$stmt->execute();

// Fetch friends with social links
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.photo, u.last_active, u.facebook, u.instagram, u.linkedin FROM users u
    JOIN friendships f ON (
        (f.user_id = u.id AND f.friend_id = ?) OR
        (f.friend_id = u.id AND f.user_id = ?)
    )
    WHERE f.status = 'accepted' AND u.id != ?
");
$stmt->bind_param("iii", $current_user, $current_user, $current_user);
$stmt->execute();
$friends = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Your Friends</title>
    </head>
    <body>
        <header>
            <h2>Your Friends</h2>
        </header>
        <main>
            <?php if ($friends->num_rows > 0): ?>
                <ul style="list-style: none; padding: 0;">
                <?php while ($row = $friends->fetch_assoc()): ?>
                    <?php
                        $photo = $row['photo'] ?? '';
                        $photoPath = '../events/uploads/' . $photo;
                        $photoURL = (file_exists(__DIR__ . '/../events/uploads/' . $photo) && !empty($photo))
                            ? $photoPath
                            : 'https://via.placeholder.com/40';

                        $lastActive = strtotime($row['last_active']);
                        $isOnline = (time() - $lastActive) < 120;
                        $statusColor = $isOnline ? 'green' : 'red';
                        $statusText = $isOnline ? 'Online' : 'Offline';
                    ?>
                    <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <span title="<?= $statusText ?>" style="width: 10px; height: 10px; background-color: <?= $statusColor ?>; border-radius: 50%; display: inline-block;"></span>
                        <img src="<?= htmlspecialchars($photoURL) ?>" alt="Friend Photo" style="width:40px; height:40px; border-radius:50%;">
                        <span style="flex: 1;"><?= htmlspecialchars($row['name']) ?></span>

                        <!-- Social Media Icons -->
                        
                        <?php if (!empty($row['facebook'])): ?>
                            <a href="<?= htmlspecialchars($row['facebook']) ?>" target="_blank" title="Facebook">
                                <img src="img/facebooklogo.png" alt="Logo">
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($row['instagram'])): ?>
                            <a href="<?= htmlspecialchars($row['instagram']) ?>" target="_blank" title="Instagram">
                                <img src="img/instagramlogo.png" alt="Logo">
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($row['linkedin'])): ?>
                            <a href="<?= htmlspecialchars($row['linkedin']) ?>" target="_blank" title="LinkedIn">
                                <img src="img/linkedinlogo.png" alt="Logo">
                            </a>
                        <?php endif; ?>

                        <!-- Unfriend Form --> 
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="unfriend_id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to unfriend this user?');">Unfriend</button>
                        </form>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no friends yet.</p>
            <?php endif; ?>
        </main>

        <div>
            <button onclick="location.href='../friends/send_friend.php'">Send Friend Request</button>
            <button onclick="location.href='../friends/accept_friend.php'">Pending Friend Requests</button>
            <button onclick="location.href='../events/list.php'">Back to List</button>
        </div>

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
            margin-top: 120px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        ul {
            width: 100%;
            max-width: 600px;
        }

        div {
            margin-bottom: 100px;
        }

        

        li img[alt="Logo"] {
            width: 50px;
            height: 50px;
            border-radius: 4px;
        }

        footer {
            margin-bottom: -8px;
            margin-top: auto;
            text-align: center;
            padding-top: 15px;
            padding-bottom: 15px;
            background-color: #4faaff;
            bottom: 0px;
            width: 100%;
        }
    </style>
</html>
