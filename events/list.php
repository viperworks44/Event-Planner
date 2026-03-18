<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

include_once __DIR__ . '/../includes/db.php';

// Get the user info including photo
$user_id = $_SESSION["user_id"];
$sql = "SELECT name, photo FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Determine photo URL
$photo_filename = $user["photo"] ?? null;
$photo_path = __DIR__ . "/uploads/" . $photo_filename;
$photo_url = ($photo_filename && file_exists($photo_path))
    ? "uploads/" . htmlspecialchars($photo_filename)
    : "https://via.placeholder.com/50"; // fallback
?>

<!DOCTYPE html>
<html lang="en">
    <body>
        <head>
            <meta charset="UTF-8">
            <title>Event Planner</title>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

                button {
                    background-color:rgb(218, 234, 219); /* Green */
                    color: black;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }

                footer p {
                    color: black;
                }
                links{
                    background-color: #4faaff;
                }

                .profile-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px;
                    gap: 10px;
                    background-color: #4faaff;
                    color: rgb(255, 255, 255);
                }
            </style>
            <div class="profile-header">
                <div class="profile-info">
                    <img src="<?= $photo_url ?>" alt="Profile Photo">
                    <h2>
                        Welcome to the Event List 
                        <strong style="color:blue;"><?php echo htmlspecialchars($user["name"]); ?></strong>
                    </h2>
                </div>
                <div class="links">
                    <button onclick="location.href='../events/profile.php'">My Profile</button>
                    <button onclick="location.href='../events/create.php'">Create Event</button>
                    <button onclick="location.href='../friends/display_friends.php'">Display Friends</button>
                    
                    <!-- 🔔 Notifications Icon with Count -->
                    <button onclick="location.href='../notifications/view_notifications.php'">🔔
                        <?php
                        $notif_stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
                        $notif_stmt->bind_param("i", $user_id);
                        $notif_stmt->execute();
                        $notif_result = $notif_stmt->get_result()->fetch_assoc();
                        $unread_count = $notif_result["unread_count"] ?? 0;

                        if ($unread_count > 0) {
                            echo "<strong style='color:red;'>($unread_count)</strong>";
                        }
                        ?>
                    </button>
                    <button onclick="location.href='../auth/logout.php'">Logout</button>

                </div>
            </div>
        </head>
    
        <div id="event-container">
            <!-- Events will be loaded here via AJAX from load_events -->
        </div>

        <script>
            // Function to load events via AJAX
            function loadEvents() {
                fetch('../events/load_events.php')
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('event-container').innerHTML = html;
                    });
            }

            // Load events immediately and every 5 seconds
            loadEvents();
            setInterval(loadEvents, 5000);

            // Save scroll position before unload
            window.addEventListener('beforeunload', () => {
                localStorage.setItem('scrollPos', window.scrollY);
            });

            // Restore scroll position after load
            window.addEventListener('load', () => {
                const scrollPos = localStorage.getItem('scrollPos');
                if (scrollPos) {
                    window.scrollTo(0, parseInt(scrollPos));
                    localStorage.removeItem('scrollPos');
                }
            });
        </script>
        <footer>
            <p>&copy; 2025 Event Planner</p>
        </footer>
    </body>
</html>
