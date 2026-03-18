<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

include_once __DIR__ . '/../includes/db.php';

$event_id = $_GET["id"] ?? null;

if (!$event_id) {
    die("Event ID missing.");
}

// Fetch the event
$sql = "SELECT * FROM events WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found or not yours.");
}

$title = $event["title"];
$date = str_replace(' ', 'T', $event["date"]);
$description = $event["description"];
$is_public = $event["public"];
$location_name = $event["location_name"] ?? '';
$location_link = $event["location_link"] ?? '';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $date = str_replace('T', ' ', $_POST["date"]);
    $description = trim($_POST["description"]);
    $is_public = isset($_POST["public"]) ? 1 : 0;
    $location_name = trim($_POST["location_name"]);
    $location_link = trim($_POST["location_link"]);

    if (empty($title)) $errors[] = "Title is required.";
    if (empty($date)) $errors[] = "Date is required.";

    if (empty($errors)) {
        $sql = "UPDATE events SET title = ?, date = ?, description = ?, public = ?, location_name = ?, location_link = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssissii", $title, $date, $description, $is_public, $location_name, $location_link, $event_id, $_SESSION["user_id"]);

        if ($stmt->execute()) {
            header("Location: list.php");
            exit();
        } else {
            $errors[] = "Error updating event: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
</head>
<body>
    <header>
        <h2>Edit Event</h2>
    </header>
    <main>  
        <?php if (!empty($errors)): ?>
            <ul style="color:red;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>          
        <form method="post">
            <label>Title:</label><br>
            <input type="text" name="title" value="<?= htmlspecialchars($title) ?>"><br><br>

            <label>Date & Time:</label><br>
            <input type="datetime-local" name="date" value="<?= htmlspecialchars(str_replace(' ', 'T', $date)) ?>"><br><br>

            <label>Description:</label><br>
            <textarea name="description" rows="5" cols="40"><?= htmlspecialchars($description) ?></textarea><br><br>

            <label>Location Name:</label><br>
            <input type="text" name="location_name" value="<?= htmlspecialchars($location_name) ?>"><br><br>

            <label>Location Link (Google Maps URL):</label><br>
            <input type="url" name="location_link" value="<?= htmlspecialchars($location_link) ?>"><br><br>

            <label>
                <input type="checkbox" name="public" value="1" <?= $is_public ? 'checked' : '' ?>>
                Make this event public
            </label><br><br>

            <input type="submit" value="Save">
        </form>  
        <br><br><a href="list.php">← Back to Event List</a>  
    </main>
    <footer>
        <p>&copy; 2025 Event Planner</p>
    </footer>
    <style>
        html,body {
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
            justify-content: center;
            margin-bottom: 80px;
        }

        footer {
            margin-top: auto;
            text-align: center;
            padding: 15px;
            background-color: #4faaff;        
            width: 100%;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</body>
</html>
