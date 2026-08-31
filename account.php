<?php
// Account Page
// A.2   - Display the logged-in user's account info.
// A.3   - Provide an edit option for name, email, password, skill level.
// A.4   - Notification icon (stubbed - no notifications table yet).
// A.5   - Provide a log out option.
// A.5.1 - Log out ends the session and redirects to Login.
//
session_start();
require_once "php/db.php";

if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: php/login.php');
    exit;
}
$currentUserId = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT user_id, name, username, email, skill_level FROM users WHERE user_id = ?");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    // Falls back to a blank shell rather than a hard crash if the placeholder
    // user_id doesn't exist yet (e.g. schema.sql hasn't been seeded).
    $user = ['user_id' => $currentUserId, 'name' => '', 'username' => '', 'email' => '', 'skill_level' => 'Beginner'];
}

// A.4
$mockNotifications = [
    ['message' => 'Your game starts in 30 minutes.', 'read' => false],
    ['message' => 'Someone joined your game.', 'read' => false],
    ['message' => 'A nearby game just opened up a spot.', 'read' => true],
];
$unreadCount = count(array_filter($mockNotifications, fn($n) => !$n['read']));

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Account - One More Game</title>
<link rel="stylesheet" href="css/account.css">
</head>
<body>

<header class="account-header">
  <a href="index.php" class="brand">One More Game</a>

  <!-- A.4 - notification icon -->
  <div class="notif-wrap">
    <button id="notifIcon" class="notif-icon" aria-label="Notifications">
      🔔
      <?php if ($unreadCount > 0): ?>
        <span class="notif-badge" id="notifBadge"><?php echo $unreadCount; ?></span>
      <?php endif; ?>
    </button>

    <!-- A.4.1 / A.4.2 (stub) - menu with recent activity -->
    <div id="notifMenu" class="notif-menu hidden">
      <h3>Notifications</h3>
      <ul id="notifList">
        <?php foreach ($mockNotifications as $n): ?>
          <li class="<?php echo $n['read'] ? 'read' : 'unread'; ?>">
            <?php echo htmlspecialchars($n['message']); ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="notif-stub-note">(Notification actions coming soon)</p>
    </div>
  </div>
</header>

<main class="account-main">
  <h1>My Account</h1>

  <!-- A.2 - account info display -->
  <section id="viewMode" class="account-card">
    <div class="field-row">
      <span class="field-label">Name</span>
      <span class="field-value" id="displayName"><?php echo htmlspecialchars($user['name']); ?></span>
    </div>
    <div class="field-row">
      <span class="field-label">Username</span>
      <span class="field-value" id="displayUsername"><?php echo htmlspecialchars($user['username']); ?></span>
    </div>
    <div class="field-row">
      <span class="field-label">Email</span>
      <span class="field-value" id="displayEmail"><?php echo htmlspecialchars($user['email']); ?></span>
    </div>
    <div class="field-row">
      <span class="field-label">Password</span>
      <span class="field-value">••••••••</span>
    </div>
    <div class="field-row">
      <span class="field-label">Skill Level</span>
      <span class="field-value" id="displaySkill"><?php echo htmlspecialchars($user['skill_level']); ?></span>
    </div>

    <!-- A.3 - edit option -->
    <button id="editBtn" class="btn btn-primary">Edit</button>
  </section>

  <!-- A.3 - edit form, hidden until Edit is clicked -->
  <section id="editMode" class="account-card hidden">
    <form id="editForm">
      <label for="nameInput">Name</label>
      <input type="text" id="nameInput" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

      <label for="emailInput">Email</label>
      <input type="email" id="emailInput" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

      <label for="passwordInput">New Password</label>
      <input type="password" id="passwordInput" name="password" placeholder="Leave blank to keep current password">

      <label for="skillInput">Skill Level</label>
      <select id="skillInput" name="skillLevel">
        <option value="Beginner" <?php echo $user['skill_level'] === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
        <option value="Intermediate" <?php echo $user['skill_level'] === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
        <option value="Competitive" <?php echo $user['skill_level'] === 'Competitive' ? 'selected' : ''; ?>>Competitive</option>
      </select>

      <div class="edit-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" id="cancelEditBtn" class="btn btn-ghost">Cancel</button>
      </div>
    </form>
  </section>

  <!-- A.5 - log out option -->
  <button id="logoutBtn" class="btn btn-danger">Log Out</button>

  <div id="accountToast" class="toast"></div>
</main>

<script>
  const CURRENT_USER_ID = <?php echo json_encode($currentUserId); ?>;
</script>
<script src="js/account.js"></script>
</body>
</html>
