<?php
// A.3 - Saves account edits to the real users table.
// POST /php/update_profile.php  { user_id, name, email, password, skillLevel }

header("Content-Type: application/json");
require_once "db.php";

$input = json_decode(file_get_contents("php://input"), true);

$userId = $input['user_id'] ?? null;
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$skillLevel = $input['skillLevel'] ?? '';

$allowedSkillLevels = ['Beginner', 'Intermediate', 'Competitive'];

if (!$userId) {
    http_response_code(400);
    echo json_encode(["error" => "user_id is required"]);
    exit;
}

if ($name === '' || $email === '') {
    echo json_encode(["success" => false, "message" => "Name and email are required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid email address."]);
    exit;
}

if (!in_array($skillLevel, $allowedSkillLevels, true)) {
    echo json_encode(["success" => false, "message" => "Invalid skill level selected."]);
    exit;
}

// Only touch password_hash if the user actually typed a new password.
if ($password !== '') {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
        UPDATE users SET name = ?, email = ?, skill_level = ?, password_hash = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("ssssi", $name, $email, $skillLevel, $passwordHash, $userId);
} else {
    $stmt = $conn->prepare("
        UPDATE users SET name = ?, email = ?, skill_level = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("sssi", $name, $email, $skillLevel, $userId);
}

try {
    $stmt->execute();
    echo json_encode([
        "success" => true,
        "user" => [
            "name" => $name,
            "email" => $email,
            "skillLevel" => $skillLevel,
        ],
    ]);
} catch (mysqli_sql_exception $e) {
    // Catches the UNIQUE constraint on users.email
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "That email is already in use."]);
}

$stmt->close();
$conn->close();
