<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) die("Login required.");

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== 'admin') die("Access denied.");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      color: #000;
      min-height: 100vh;
    }
    .overlay {
      background: rgba(255, 255, 255, 0.95);
      min-height: 100vh;
      padding-top: 60px;
    }
    .dashboard-box {
      background: white;
      padding: 40px;
      border-radius: 12px;
      max-width: 600px;
      margin: 60px auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .logout-btn {
      position: absolute;
      top: 15px;
      right: 20px;
    }
    .back-btn {
      position: absolute;
      top: 15px;
      left: 20px;
    }
    .btn-custom {
      padding: 12px 25px;
      font-size: 1rem;
    }
  </style>
</head>
<body>
  <div class="overlay">
    <!-- Back and Logout buttons -->
    <a href="login.php" class="btn btn-outline-secondary back-btn">← Back to Login</a>
    <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>

    <div class="dashboard-box text-center">
      <h2>Welcome Admin 👨‍🔧</h2>
      <p class="alert alert-info mt-3">You can register technicians, manage appointments, and oversee garage activities.</p>

      <div class="d-grid gap-3 mt-4">
        <a href="manage-technicians.php" class="btn btn-warning btn-custom">Manage Technicians</a>
        <a href="view-appointments.php" class="btn btn-secondary btn-custom">View Appointments</a>
        <a href="register-center.php" class="btn btn-primary btn-custom">Register Service Center</a>
        <a href="manage-centers.php" class="btn btn-info btn-custom">Manage Service Centers</a>
        <a href="change-password.php" class="btn btn-outline-dark btn-custom">Change Password</a>
      </div>
    </div>
  </div>
</body>
</html>



