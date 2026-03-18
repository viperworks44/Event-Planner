<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

include_once __DIR__ . '/../includes/db.php';

$user_id = $_SESSION["user_id"];
$errors = [];

// Fetch user details (used for both form and old photo deletion)
$sql = "SELECT name, email, photo, facebook, instagram, linkedin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$old_photo = $user["photo"];
$old_photo_path = __DIR__ . '/uploads/' . $old_photo;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $facebook = $_POST["facebook"] ?? null;
    $instagram = $_POST["instagram"] ?? null;
    $linkedin = $_POST["linkedin"] ?? null;

    // Handle image upload
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $filename = "user_" . $user_id . "_" . time() . "." . $ext;
        $uploadPath = $uploadDir . "/" . $filename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadPath)) {
            if (!empty($old_photo) && file_exists($old_photo_path)) {
                unlink($old_photo_path);
            }
            $sql = "UPDATE users SET photo = ?, facebook = ?, instagram = ?, linkedin = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $filename, $facebook, $instagram, $linkedin, $user_id);
        } else {
            $errors[] = "Failed to move uploaded file.";
        }
    } else {
        // No new photo uploaded, just update social links
        $sql = "UPDATE users SET facebook = ?, instagram = ?, linkedin = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $facebook, $instagram, $linkedin, $user_id);
    }

    if (!isset($errors[0]) && !$stmt->execute()) {
        $errors[] = "Database update failed: " . $stmt->error;
    } else {
        header("Location: profile.php");
        exit();
    }
}

// For displaying photo
$photo_url = $user["photo"] && file_exists("uploads/" . $user["photo"])
    ? "uploads/" . htmlspecialchars($user["photo"])
    : "https://via.placeholder.com/150";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<body>
<header>
    <h2>My Profile</h2>
</header>
<main>
    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><strong>Name:</strong> <?= htmlspecialchars($user["name"]) ?> , <strong> Email:</strong> <?= htmlspecialchars($user["email"]) ?></p>
    <p><strong>Profile Photo:</strong></p>
    <img src="<?= $photo_url ?>" width="150" height="150" alt="Profile Photo"><br>

    <form method="post" enctype="multipart/form-data">
        <label>Change Profile Photo:</label><br>
        <input type="file" name="photo" accept="image/*"><br><br>

        <label>Facebook URL: (optional)</label><br>
        <input type="url" name="facebook" value="<?= htmlspecialchars($user["facebook"] ?? '') ?>" placeholder="https://facebook.com/yourprofile"><br>

        <label>Instagram URL: (optional)</label><br>
        <input type="url" name="instagram" value="<?= htmlspecialchars($user["instagram"] ?? '') ?>" placeholder="https://instagram.com/yourprofile"><br>

        <label>LinkedIn URL: (optional)</label><br>
        <input type="url" name="linkedin" value="<?= htmlspecialchars($user["linkedin"] ?? '') ?>" placeholder="https://linkedin.com/in/yourprofile"><br>

        <input type="submit" value="Save Changes">
    </form>

    
    <br><a href="list.php">← Back to Events</a><br>
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
        margin-top: 100px;
        display: flex;
        flex-direction: column;
        align-items: center;
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

    input[type="url"], input[type="file"] {
        width: 300px;
    }
</style>
</body>
</html>
