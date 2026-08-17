<?php
// Main Page - M.3 / M.3.1
// M.3   - The main page shall display a browse games button.
// M.3.1 - The browse games button shall redirect the user to the browse page when clicked.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>One More Game</title>
<link rel="stylesheet" href="css/main.css">
</head>
<body>

<header>
  <div class="brand">One More Game</div>
  <nav>
    <a href="account.php" class="nav-btn" id="accountBtn">Account</a>
    <a href="court_map.php" class="nav-btn" id="courtMapBtn">Court Map</a>

    <!-- M.3: browse games button -->
    <a href="browse.php" class="nav-btn" id="browseGamesBtn">Browse Games</a>

    <a href="create.php" class="nav-btn" id="createGameBtn">Create Game</a>
  </nav>
</header>

<main>
  <h1>Find your next run.</h1>
  <p>Live status for every court. No group chats, no guessing.</p>

  <div class="cta-row">
    <a href="create.php" class="btn btn-primary">+ Create a Game</a>
    <!-- M.3.1: clicking this redirects to browse.php -->
    <a href="browse.php" class="btn btn-ghost" id="browseGamesCta">Browse Games</a>
  </div>
</main>

</body>
</html>