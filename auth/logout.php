<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

</body>
</html>
<?php
// Start the session
session_start();

// Unset all session variables
session_unset();
// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: ../index.php");
exit();
?>