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
            <h2>Welcome to our Event Planner</h2>
        </header>
        <main>
            <button onclick="location.href='auth/login.php'">Login</button><br>
            <button onclick="location.href='auth/register.php'">Register</button><br>
        </main>
        <footer>
            <p>&copy; 2025 Event Planner</p>
        </footer>
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
    </body>
</html>


