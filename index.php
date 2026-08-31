<?php
session_start();
if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: php/login.php');
    exit;
}
$username = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Player';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>One More Game</title>

    <link rel="stylesheet" href="style.css">


<!-- From Leafletjs.com -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>


</head>

<body>

    <!-- Header and Navigation -->

    <header>

        <h1>🏀 One More Game</h1>
        <div class="user-bar">Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?> · <a href="php/logout.php?redirect=1">Log Out</a></div>

        <nav>

            <button onclick="window.location.href='account.php'">
                Account
            </button>

            <button onclick="showSection('map')">
                Court Map
            </button>

            <button onclick="showSection('browse')">
                Browse Games
            </button>

            <button onclick="showSection('game')">
                Create Game
            </button>
                <button onclick="showSection('home')">
                    Court Status
                </button>


        </nav>

    </header>


    <main>

        <!-- Home Page / Court Status -->

        <section id="home" class="section active">

            <h2>Court Status</h2>

            <p class="intro">
                Check which courts have active games and which courts are available.
            </p>

            <div class="status-container">

                <!-- Court 1 -->

                <div class="status-card">

                    <h3>🏀 Recreation Center</h3>

                    <p class="available">
                        Available
                    </p>

                    <p>
                        Open for a new game
                    </p>

                </div>


                <!-- Court 2 -->

                <div class="status-card">

                    <h3>🏀 Walking Trail
                    </h3>

                    <p class="active-game">
                        Active Game
                    </p>

                    <p>
                        Game: 3 vs 3
                    </p>

                    <p>
                        Players: 4 / 6
                    </p>

                </div>

                </div>

            </section>


        <!-- Court Map Page -->

        <section id="map" class="section">
        <h2>Court Map</h2>
        <p class="intro">View the current status of each basketball court.</p>
        <div id="leafletMap"></div>

            </section>

        <!-- Browse Games Page -->

        <section id="browse" class="section">

            <h2>Browse Games</h2>

            <p class="intro">
                Browse available basketball games and join a game.
            </p>


            <div class="browse-filters" aria-label="Game filters">
                <select id="filterFormat">
                    <option value="">All formats</option>
                    <option value="5v5">5v5</option>
                    <option value="4v4">4v4</option>
                    <option value="3v3">3v3</option>
                    <option value="2v2">2v2</option>
                </select>

                <select id="filterCourt">
                    <option value="">All courts</option>
                </select>

                <select id="filterSkill">
                    <option value="">All levels</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Competitive">Competitive</option>
                </select>

                <button type="button" id="applyFilters">Apply Filters</button>
                <button type="button" id="clearFilters" class="secondary-filter">Clear</button>
            </div>

            <div id="gameList" class="game-list">
                <p>Loading games...</p>
            </div>

            <p id="joinMessage"></p>

        </section>


        <!-- Create Game Page -->

        <section id="game" class="section">

            <h2>Create a Game</h2>

            <form id="gameForm">

                <!-- G.2 - location input, populated dynamically from the courts table -->
                <label for="courtSelect">
                    Select a Court
                </label>

                <select id="courtSelect" required>
                    <option value="">
                        Select a court
                    </option>
                </select>

                <!-- G.2.1 - game format selector -->
                <label for="gameType">
                    Game Format
                </label>

                <select id="gameType" required>
                    <option value="">Select game format</option>
                    <option value="5v5">5v5</option>
                    <option value="4v4">4v4</option>
                    <option value="3v3">3v3</option>
                    <option value="2v2">2v2</option>
                </select>

                <!-- G.2.2 - skill level selector -->
                <label for="gameSkill">
                    Skill Level
                </label>

                <select id="gameSkill" required>
                    <option value="">Select skill level</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Competitive">Competitive</option>
                </select>

                <!-- G.2 / G.4 - start time, defaults to current time -->
                <label for="gameDate">
                    Game Date
                </label>

                <input type="date" id="gameDate" required>

                <label for="gameTime">
                    Start Time
                </label>

                <input type="time" id="gameTime" required>

                <!-- G.2 - maximum number of players -->
                <label for="maxPlayers">
                    Max Players
                </label>

                <input type="number" id="maxPlayers" min="2" max="20" required>

                <!-- G.2.3 - description field -->
                <label for="gameDescription">
                    Description
                </label>

                <textarea id="gameDescription" maxlength="255"
                    placeholder="Notes on player group or other game details (optional)"></textarea>

                <button type="submit" id="gameSubmitButton">
                    Create Game
                </button>

                <button type="button" class="cancel-button" id="clearGameButton" onclick="cancelGame()">
                    Clear Form
                </button>

            </form>

            <p id="gameMessage"></p>

        </section>


    </main>


    <footer>

        <p>
            🏀 One More Game
        </p>

    </footer>


    <script src="script.js"></script>

</body>

</html>
