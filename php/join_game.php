<?php
// B.1.2.1 - add the user to the game roster when the join button is selected
// B.1.2.2 - reject the join if the game has reached its maximum number of players
// POST /php/join_game.php  { game_id, user_id }

header("Content-Type: application/json");
require_once "db.php";

$input = json_decode(file_get_contents("php://input"), true);
$gameId = $input['game_id'] ?? null;
$userId = $input['user_id'] ?? null;

if (!$gameId || !$userId) {
    http_response_code(400);
    echo json_encode(["error" => "game_id and user_id are required"]);
    exit;
}

// Check current roster size vs max_players
$check = $conn->prepare("
    SELECT g.max_players, (SELECT COUNT(*) FROM roster r WHERE r.game_id = g.game_id) AS current_players
    FROM games g WHERE g.game_id = ?
");
$check->bind_param("i", $gameId);
$check->execute();
$result = $check->get_result()->fetch_assoc();

if (!$result) {
    http_response_code(404);
    echo json_encode(["error" => "Game not found"]);
    exit;
}

if ($result['current_players'] >= $result['max_players']) {
    http_response_code(409);
    echo json_encode(["error" => "This game is full", "is_full" => true]);
    exit;
}

$insert = $conn->prepare("INSERT INTO roster (game_id, user_id) VALUES (?, ?)");
$insert->bind_param("ii", $gameId, $userId);

try {
    $insert->execute();
    echo json_encode(["success" => true, "message" => "You're in. See you on the court."]);
} catch (mysqli_sql_exception $e) {
    // UNIQUE KEY unique_join catches duplicate join attempts
    http_response_code(409);
    echo json_encode(["error" => "You've already joined this game"]);
}

$insert->close();
$check->close();
$conn->close();
