<?php
// Capstone Project - One More Game
// Omar Elzahri
// Test Home Page

session_start();

if (empty($_SESSION['is_logged_in'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Player';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One More Game | Home</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            color: #fff;
            background: #101010;
        }
        .card {
            width: min(90%, 560px);
            padding: 40px;
            border: 1px solid #2b2b2b;
            border-radius: 16px;
            background: #171717;
            text-align: center;
        }
        h1 { margin-top: 0; }
        span { color: #ff7a00; }
        p { color: #b5b5b5; line-height: 1.6; }
        a {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 20px;
            border-radius: 8px;
            color: #fff;
            background: #f06d00;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Welcome, <span><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span> 🏀</h1>
        <p>Your login session is active. This page is protected, so a user has to log in before they can see it.</p>
        <a href="logout.php">Log Out</a>
    </main>
</body>
</html>
