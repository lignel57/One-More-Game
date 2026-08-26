<?php
// Supporting endpoint for the Game Creation page (G.2 - location input)
// GET /php/courts_list.php
// Returns all courts so the "Select a Court" dropdown reflects real data.

header("Content-Type: application/json");
require_once "db.php";

$result = $conn->query("SELECT court_id, name, status FROM courts ORDER BY name ASC");

$courts = [];
while ($row = $result->fetch_assoc()) {
    $courts[] = $row;
}

echo json_encode(["courts" => $courts]);

$conn->close();
