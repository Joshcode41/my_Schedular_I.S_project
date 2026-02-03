<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$message = "";
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $current = $_POST['current_password'];
  $new = $_POST['new_password'];
  $confirm = $_POST['confirm_password'];

  // Fetch current password hash
  $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($hashed);
  $stmt->fetch();
  $stmt->close();

  if (!password_verify($current, $hashed)) {
    $message = '<div class="alert alert-danger">❌ Incorrect current password.</div>';
  } elseif ($new !== $confirm) {
    $message = '<div class="alert alert-danger">❗ New passwords do not match.</div>';
  } else {
    $new_hashed = password_hash($new, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET password = ?, password_changed = 1 WHERE id = ?");
    $update->bind_param("si", $new_hashed, $user_id);
    $update->execute();
    $message = '<div class="alert alert-success">✅ Password updated successfully!</div>';
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Change Password - Star Garage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .overlay {
      background: rgba(255,255,255,0.95);
      min-height: 100vh;
      padding-top: 80px;
    }
    .box {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .back-btn {
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .logout-btn {
      position: absolute;
      top: 20px;
      right: 20px;
    }
  </style>
</head>
<body>

<div class="overlay">
  <a href="javascript:history.back()" class="btn btn-outline-secondary back-btn">← Back</a>
  <a href="logout.php" class="btn btn-outline-danger logout-btn">Logout</a>

  <div class="box">
    <h3 class="text-center mb-4">🔒 Change Password</h3>

    <?= $message ?>

    <form method="post" autocomplete="off">
      <div class="mb-3">
        <label>Current Password</label>
        <div class="input-group">
          <input type="password" name="current_password" class="form-control" required>
          <button class="btn btn-outline-secondary toggle-visibility" type="button">👁️</button>
        </div>
      </div>
      <div class="mb-3">
        <label>New Password</label>
        <div class="input-group">
          <input type="password" name="new_password" class="form-control" required>
          <button class="btn btn-outline-secondary toggle-visibility" type="button">👁️</button>
        </div>
      </div>
      <div class="mb-3">
        <label>Confirm New Password</label>
        <div class="input-group">
          <input type="password" name="confirm_password" class="form-control" required>
          <button class="btn btn-outline-secondary toggle-visibility" type="button">👁️</button>
        </div>
      </div>
      <button type="submit" class="btn btn-dark w-100">Update Password</button>
    </form>
  </div>
</div>

<script>
  // Toggle password visibility
  document.querySelectorAll('.toggle-visibility').forEach(btn => {
    btn.addEventListener('click', function () {
      const input = this.previousElementSibling;
      if (input.type === 'password') {
        input.type = 'text';
        this.textContent = '🙈';
      } else {
        input.type = 'password';
        this.textContent = '👁️';
      }
    });
  });
</script>

</body>
</html>

