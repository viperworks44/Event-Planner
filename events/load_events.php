<?php
session_start();
include_once __DIR__ . '/../includes/db.php';
date_default_timezone_set('Europe/Athens');

// Simulate login (for development/testing only)
if (isset($_GET['impersonate']) && is_numeric($_GET['impersonate'])) {
    $_SESSION['user_id'] = (int)$_GET['impersonate'];
}

// Make sure user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo "Not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get friend IDs and self
$friends_sql = "SELECT friend_id FROM friendships WHERE user_id = ? AND status = 'accepted'
                UNION 
                SELECT user_id FROM friendships WHERE friend_id = ? AND status = 'accepted'";
$stmt = $conn->prepare($friends_sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$friend_result = $stmt->get_result();

$friend_ids = [$user_id]; // Include self
while ($row = $friend_result->fetch_assoc()) {
    $friend_ids[] = $row["friend_id"];
}
$placeholders = implode(",", array_fill(0, count($friend_ids), "?"));

// Load events that are public OR created by friends, and are today or in the future
$sql = "SELECT events.*, users.name AS organizer 
        FROM events 
        JOIN users ON events.user_id = users.id 
        WHERE (events.public = 1 OR events.user_id IN ($placeholders))
        AND events.date >= CURDATE()
        ORDER BY events.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("i", count($friend_ids)), ...$friend_ids);
$stmt->execute();
$result = $stmt->get_result();

// Format time difference
function formatTimeAgo($created_at) {
    $now = new DateTime();
    $created = new DateTime($created_at);
    $diff = $now->getTimestamp() - $created->getTimestamp();

    if ($diff < 60) return "Posted: Now";
    if ($diff < 3600) return "Posted: " . floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return "Posted: " . floor($diff / 3600) . " hours ago";
    if ($diff < 604800) return "Posted: " . floor($diff / 86400) . " days ago";
    return "Posted: A long time ago";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Feed</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        footer {
            margin-top: auto;
            text-align: center;
            padding: 15px;
            background-color: #4faaff;
            bottom: 0;
            width: 100%;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            gap: 10px;
            background-color: #4faaff;
            color: rgb(0, 0, 0);
        }


        .links {    
            background-color: #4faaff;
            border-radius: 5px;
            text-decoration: none;
            flex-wrap: wrap;
            align-items: center;
        }

        .event-container {
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        .event-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #a7cff5ff;
        }

        .event-card a {
            color: #007bff;
            text-decoration: none;
        }

        .event-card a:hover {
            text-decoration: underline;
        }

        .public-event {
            color: green;
        }

        .private-event {
            color: orange;
        }
        
    </style>
</head>
<body>
    <div class="main">
        <div class="event-container">
        <?php while ($row = $result->fetch_assoc()): 
            // Count RSVPs
            $stmt2 = $conn->prepare("SELECT COUNT(*) AS count FROM rsvps WHERE event_id = ?");
            $stmt2->bind_param("i", $row["id"]);
            $stmt2->execute();
            $count_result = $stmt2->get_result();
            $count = $count_result->fetch_assoc()["count"];

            $total_participants = $count + 1;
        ?>
            <div class="event-card">
                <p><strong><?= formatTimeAgo($row["created_at"]) ?></strong></p>
                <h2><?= htmlspecialchars($row["title"]) ?></h2>
                <p><strong>Date:</strong> <?= htmlspecialchars($row["date"]) ?></p>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($row["description"])) ?></p>
                <p><strong>Created by:</strong> <?= htmlspecialchars($row["organizer"]) ?></p>

                <?php if (!empty($row["location_name"]) && !empty($row["location_link"])): ?>
                    <p><strong>Location:</strong>
                        <a href="<?= htmlspecialchars($row["location_link"]) ?>" target="_blank">
                            <?= htmlspecialchars($row["location_name"]) ?>
                        </a>
                    </p>
                <?php else: ?>
                    <p><strong>Location:</strong> Not specified</p>
                <?php endif; ?>

                <p><strong>Participants:</strong> <?= $total_participants ?></p>
                <p>
                    <strong class="<?= $row["public"] ? 'public-event' : 'private-event' ?>">
                        <?= $row["public"] ? 'Public Event' : 'Friends Only' ?>
                    </strong>
                </p>

                <?php if ($_SESSION["user_id"] == $row["user_id"]): ?>
                    <p><a href="../events/edit.php?id=<?= $row["id"] ?>"><em style="color:blue;">Edit this event</em></a></p>
                <?php else: ?>
                    <p><a href="../events/rsvp.php?id=<?= $row["id"] ?>">Participate in this event</a></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
