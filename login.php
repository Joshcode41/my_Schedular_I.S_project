<?php
session_start();
require 'db.php';

$redirectTo = isset($_GET['redirect']) ? $_GET['redirect'] : null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password, role, username, password_changed FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password, $role, $username, $password_changed);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $username;

            // If technician and first login
            if ($role === 'technician' && !$password_changed) {
                header("Location: change-password.php");
                exit();
            }

            // Redirect based on role or redirect param
            if ($redirectTo && !str_contains($redirectTo, 'logout')) {
                header("Location: " . htmlspecialchars($redirectTo));
            } elseif ($role === 'admin') {
                header("Location: admin-dashboard.php");
            } elseif ($role === 'client') {
                header("Location: client-dashboard.php");
            } else {
                header("Location: technician-dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Account not found.";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login - Star Garage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      max-width: 400px;
      margin: 80px auto;
      padding: 40px;
      border-radius: 10px;
    }
    .back-btn {
      position: absolute;
      top: 15px;
      left: 15px;
    }
  </style>
</head>
<body>

  <!-- Back button to index.html -->
  <a href="index.php" class="btn btn-outline-secondary back-btn">← Back to Home</a>

  <div class="login-container shadow">
    <h3 class="text-center mb-4"> 🔐Login to Star Garage</h3>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label>Email address</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>

</body>
</html>



