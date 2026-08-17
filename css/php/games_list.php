<?php
// B.1 - The browse page shall display a list of existing games available to join.
// GET /php/games_list.php
// Optional query params for B.2 filters: format, court_id, skill_level

header("Content-Type: application/json");
require_once "db.php";

$where = ["g.status = 'active'", "g.start_time >= NOW()"]; // hide cancelled/past games
$params = [];
$types = "";

// B.2 - filters for game format, location (court), and skill level
if (!empty($_GET['format'])) {
    $where[] = "g.format = ?";
    $params[] = $_GET['format'];
    $types .= "s";
}
if (!empty($_GET['court_id'])) {
    $where[] = "g.court_id = ?";
    $params[] = $_GET['court_id'];
    $types .= "i";
}
if (!empty($_GET['skill_level'])) {
    $where[] = "g.skill_level = ?";
    $params[] = $_GET['skill_level'];
    $types .= "s";
}

$whereClause = implode(" AND ", $where);

$sql = "
    SELECT
        g.game_id,
        g.format,
        g.start_time,
        g.max_players,
        g.skill_level,
        g.description,
        u.name AS host_name,
        c.name AS court_name,
        c.court_id,
        (SELECT COUNT(*) FROM roster r WHERE r.game_id = g.game_id) AS current_players
    FROM games g
    JOIN users u ON g.host_id = u.user_id
    JOIN courts c ON g.court_id = c.court_id
    WHERE $whereClause
    ORDER BY g.start_time ASC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$games = [];
while ($row = $result->fetch_assoc()) {
    // B.1.1 - roster is fetched separately per game so the front end can display it
    $rosterStmt = $conn->prepare("
        SELECT u.name FROM roster r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.game_id = ?
    ");
    $rosterStmt->bind_param("i", $row['game_id']);
    $rosterStmt->execute();
    $rosterResult = $rosterStmt->get_result();

    $roster = [];
    while ($player = $rosterResult->fetch_assoc()) {
        $roster[] = $player['name'];
    }

    // B.1.2.2 - flag whether the join button should be disabled
    $row['roster'] = $roster;
    $row['is_full'] = ($row['current_players'] >= $row['max_players']);

    // B.3 - if this game is full, look for open spots at other courts
    if ($row['is_full']) {
        $altStmt = $conn->prepare("
            SELECT
                c.name AS court_name,
                g2.game_id,
                g2.max_players,
                (SELECT COUNT(*) FROM roster r2 WHERE r2.game_id = g2.game_id) AS current_players
            FROM games g2
            JOIN courts c ON g2.court_id = c.court_id
            WHERE g2.status = 'active'
              AND g2.start_time >= NOW()
              AND g2.court_id != ?
            HAVING current_players < max_players
            ORDER BY g2.start_time ASC
            LIMIT 1
        ");
        $altStmt->bind_param("i", $row['court_id']);
        $altStmt->execute();
        $altResult = $altStmt->get_result()->fetch_assoc();

        $row['alternative'] = $altResult ? [
            "court_name" => $altResult['court_name'],
            "open_spots" => $altResult['max_players'] - $altResult['current_players']
        ] : null;

        $altStmt->close();
    } else {
        $row['alternative'] = null;
    }

    $games[] = $row;
}

echo json_encode(["games" => $games]);

$stmt->close();
$conn->close();