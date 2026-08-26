<?php
// Game Creation Page backend
// G.1   - Create a game once all required fields are completed by an authenticated user.
// G.1.1 - Return an error message if required fields are incomplete.
// G.2   - Accepts location (court_id), start time, and max players.
// G.2.1 - Validates game format against 5v5/4v4/3v3/2v2.
// G.2.2 - Validates skill level.
// G.2.3 - Accepts an optional description field.
//
// POST /php/create_game.php
// Body (JSON): { court_id, format, start_time, max_players, skill_level, description }

session_start();
header("Content-Type: application/json");
require_once "db.php";

// Same placeholder session pattern used in account.php until login (A.1) is wired up.
$currentUserId = $_SESSION['user_id'] ?? 1;

$data = json_decode(file_get_contents("php://input"), true);

// G.1.1 - required field check
$required = ["court_id", "format", "start_time", "max_players", "skill_level"];
$missing = [];
foreach ($required as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === "") {
        $missing[] = $field;
    }
}
if (!empty($missing)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Please complete all required fields."]);
    exit;
}

// G.2.1 - validate format against the schema's allowed values
$validFormats = ["5v5", "4v4", "3v3", "2v2"];
if (!in_array($data["format"], $validFormats, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid game format selected."]);
    exit;
}

// G.2.2 - validate skill level against the schema's allowed values
$validSkillLevels = ["Beginner", "Intermediate", "Competitive"];
if (!in_array($data["skill_level"], $validSkillLevels, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid skill level selected."]);
    exit;
}

$courtId = filter_var($data["court_id"], FILTER_VALIDATE_INT);
$maxPlayers = filter_var($data["max_players"], FILTER_VALIDATE_INT);

if ($courtId === false) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid court selected."]);
    exit;
}
if ($maxPlayers === false || $maxPlayers < 2 || $maxPlayers > 20) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Max players must be a number between 2 and 20."]);
    exit;
}

$startTime = $data["start_time"]; // formatted as "YYYY-MM-DD HH:MM:SS" by script.js
$description = isset($data["description"]) ? substr(trim($data["description"]), 0, 255) : null;
$format = $data["format"];
$skillLevel = $data["skill_level"];

// G.1 - insert the game
$stmt = $conn->prepare("
    INSERT INTO games (host_id, court_id, format, start_time, max_players, skill_level, description, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
");
$stmt->bind_param(
    "iississ",
    $currentUserId, $courtId, $format, $startTime, $maxPlayers, $skillLevel, $description
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error while creating the game."]);
    exit;
}

$gameId = $stmt->insert_id;
$stmt->close();

// Host is automatically added to the game's roster
$rosterStmt = $conn->prepare("INSERT INTO roster (game_id, user_id) VALUES (?, ?)");
$rosterStmt->bind_param("ii", $gameId, $currentUserId);
$rosterStmt->execute();
$rosterStmt->close();

echo json_encode(["success" => true, "game_id" => $gameId]);

$conn->close();
