<?php
// A.5 / A.5.1 - Ends the logged-in session.
// The redirect to main.php happens client-side in js/account.js after this succeeds.

session_start();
$_SESSION = [];
session_destroy();

header("Content-Type: application/json");
echo json_encode(["success" => true]);
