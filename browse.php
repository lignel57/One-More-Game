<?php
// Browse Page - B.1
// This page just renders the shell; the actual game list is fetched client-side
// from php/games_list.php so filters (B.2) can re-query without a full page reload.
session_start();
$currentUserId = $_SESSION['user_id'] ?? 1; // placeholder until login/session is wired up
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Browse Games - One More Game</title>
<link rel="stylesheet" href="css/browse.css">
</head>
<body>

<h1>Browse Games</h1>

<!-- B.2 - filters for format, court/location, skill level -->
<div class="filters">
  <select id="filterFormat">
    <option value="">All formats</option>
    <option value="5v5">5v5</option>
    <option value="4v4">4v4</option>
    <option value="3v3">3v3</option>
    <option value="2v2">2v2</option>
  </select>

  <select id="filterSkill">
    <option value="">All levels</option>
    <option value="Beginner">Beginner</option>
    <option value="Intermediate">Intermediate</option>
    <option value="Competitive">Competitive</option>
  </select>

  <button id="applyFilters">Apply Filters</button>
</div>

<!-- B.1 - list of existing games renders here -->
<div id="gameList" class="game-list">
  <p>Loading games...</p>
</div>

<div id="toast" class="toast"></div>

<script>
  const CURRENT_USER_ID = <?php echo json_encode($currentUserId); ?>;
</script>
<script src="js/browse.js"></script>
</body>
</html>