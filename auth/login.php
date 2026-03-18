<?php
session_start();
$error = "";

include_once __DIR__ . '/../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: ../events/list.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<header>
    <h2>Login to your account</h2>
</header>
<main>
    <form action="login.php" method="post">
        <label>Email:</label><br>
        <input type="email" name="email" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <input type="submit" value="Log In"><br>
        <a href="register.php">Register</a><br>
        <a href="../index.php">Home</a><br>
    </form>
</main>
<footer>
    <p>&copy; 2025 Event Planner</p>
</footer>

<?php if (!empty($error)): ?>
<script>
    alert("<?= htmlspecialchars($error, ENT_QUOTES) ?>");
</script>
<?php endif; ?>

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
