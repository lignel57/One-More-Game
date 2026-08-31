<?php
// Legacy route kept for old bookmarks/links.
session_start();
if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: php/login.php');
    exit;
}
header('Location: index.php#browse');
exit;
