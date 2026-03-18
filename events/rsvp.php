<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Planner</title>
</head>
<body>
    <header>
        <h2>RSVP Planner</h2>
    </header>
    <main>
        <?php
        session_start();

        if (!isset($_SESSION["user_id"])) {
            header("Location: ../auth/login.php");
            exit();
        }

        include_once __DIR__ . '/../includes/db.php';

        $event_id = $_GET["id"] ?? null;
        $user_id = $_SESSION["user_id"];

        if (!$event_id) {
            echo "<p>No event ID provided.</p>";
        } else {
            $rsvp_status = "";
            $message = "";

            // Check if already RSVP'd
            $sql = "SELECT * FROM rsvps WHERE user_id = ? AND event_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $rsvp_status = "already";

                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel"])) {
                    $delete_sql = "DELETE FROM rsvps WHERE user_id = ? AND event_id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("ii", $user_id, $event_id);
                    if ($delete_stmt->execute()) {
                        echo "<script>alert('RSVP cancelled successfully.'); window.location.href = '../events/list.php';</script>";
                        exit();
                    } else {
                        $message = "Error cancelling RSVP.";
                    }
                }
            } else {
                // Insert new RSVP
                $sql = "INSERT INTO rsvps (user_id, event_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $event_id);

                if ($stmt->execute()) {
                    $rsvp_status = "new";

                    // Fetch event creator and title
                    $stmt = $conn->prepare("SELECT user_id, title FROM events WHERE id = ?");
                    $stmt->bind_param("i", $event_id);
                    $stmt->execute();
                    $event_result = $stmt->get_result();
                    $event = $event_result->fetch_assoc();
                    $creator_id = $event['user_id'] ?? null;
                    $event_title = $event['title'] ?? 'an event';

                    // Get user name
                    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user_result = $stmt->get_result();
                    $user = $user_result->fetch_assoc();
                    $user_name = $user['name'] ?? 'Someone';

                    // Send notification
                    if ($creator_id) {
                        $note = "$user_name has RSVP'd to your event: \"$event_title\".";
                        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $stmt->bind_param("is", $creator_id, $note);
                        $stmt->execute();
                    }
                } else {
                    $message = "Error: " . $stmt->error;
                }
            }
        }
        ?>

        <?php if ($event_id): ?>
            <?php if ($rsvp_status === "already"): ?>
                <p>You have already RSVP'd to this event.</p>
                <?php if (!empty($message)) echo "<p>$message</p>"; ?>
                <form method="post">
                    <input type="submit" name="cancel" value="Cancel RSVP">
                </form>
            <?php elseif ($rsvp_status === "new"): ?>
                <p>You have successfully RSVP'd to the event!</p>
            <?php elseif (!empty($message)): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <br><a href="list.php">← Back to Event List</a>
        <?php endif; ?>
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
            margin: 0;
            padding: 0;
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
            margin-top: 60px;
            margin-bottom: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
