<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <header>
            <h2>Please create an account</h2>
        </header>
        <main>    
            <form action="register.php" method="post">
                <label>username:</label><br>
                <input type="text" name="name"><br>
                <label>email:</label><br>
                <input type="email" name="email"><br>
                <label>password:</label><br>
                <input type="password" name="password"><br>
                <input type="submit" value="Register"><br>
            </form>
        </main>
        <footer>
            <p>&copy; 2025 Event Planner</p>
        </footer>
    </body>
    <style>
        html,body{
            display:flex;
            flex-direction:column;
            align-items: center;
            justify-content:stretch;
            height: 100%;
            width: 100%;
        }
            
        header {
            background-color: #4faaff;
            padding: 10px;
            text-align: center;
            position: fixed;
            top:0;
            width: 100%;
        }

        main {
            flex: 1;
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
</html>
<?php
    //establish a connection to the database
    include_once __DIR__ . '/../includes/db.php';

    // Check if the form is submitted   
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Print
        echo "{$_POST["name"]} <br>";
        echo "{$_POST["email"]} <br>";
        echo "{$_POST["password"]} <br>";

        // Check if the form is submitted
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        
        // filter the input
        $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $password = filter_var($password, FILTER_SANITIZE_SPECIAL_CHARS);

        //hashing the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Display the hashed password  
        echo "Hashed Password: $hashedPassword <br>";

        // check if the email already exists
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "Email already exists. Please use a different email.";
            $checkStmt->close();
            exit();
        }
        // Close the check statement
        $checkStmt->close();

        // Prepare SQL insert
        $sql = "InSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?,NOW())";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        // Do Query
        if ($stmt->execute()) {
            echo "New record created successfully, you can now Log In.";
            header("Location: login.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        // Close the statement and connection
        $stmt->close();
    }
?>