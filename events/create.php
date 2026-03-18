<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

include_once __DIR__ . '/../includes/db.php';

$title = $datetime = $description = $location_name = $location_link = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $datetime = $_POST["date"];
    $description = trim($_POST["description"]);
    $location_name = trim($_POST["location_name"]);
    $location_link = trim($_POST["location_link"]);

    $date = str_replace("T", " ", $datetime);

    if (empty($title)) $errors[] = "Title is required.";
    if (empty($datetime)) $errors[] = "Date is required.";
    if (!empty($location_link) && !filter_var($location_link, FILTER_VALIDATE_URL)) {
        $errors[] = "Location link must be a valid URL.";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO events (user_id, title, date, description, location_name, location_link, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $_SESSION["user_id"], $title, $datetime, $description, $location_name, $location_link);

        if ($stmt->execute()) {
            header("Location: list.php");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Event Planner</title>
</head>
<body>
    <header>
        <h2>Create a New Event</h2>
    </header>
    <main>
        <?php if (!empty($errors)): ?>
            <ul style="color: red;">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="">
            <label>Title:</label><br>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>"><br><br>

            <label>Date & Time:</label><br>
            <input type="datetime-local" name="date" value="<?php echo htmlspecialchars($datetime); ?>"><br><br>

            <label>Description:</label><br>
            <textarea name="description" rows="5" cols="40"><?php echo htmlspecialchars($description); ?></textarea><br><br>

            <label>Location Name (e.g., Athens):</label><br>
            <input type="text" name="location_name" value="<?php echo htmlspecialchars($location_name); ?>"><br><br>

            <label>Location Link (Google Maps URL):</label><br>
            <input type="text" name="location_link" value="<?php echo htmlspecialchars($location_link); ?>"><br><br>

            <input type="submit" value="Create Event">
        </form>

        <br><br><a href="list.php">← Back to Event List</a>
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
        margin-top: 120px;
        margin-bottom: 80px;
    }

    footer {
        margin-top: auto;
        text-align: center;
        padding: 15px;
        background-color: #4faaff;
        bottom: 0;
        width: 100%;
    }

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    input[type="text"], textarea, input[type="datetime-local"] {
        width: 250px;
        padding: 5px;
    }
</style>
</html>
