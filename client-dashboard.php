<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== 'client') die("Access denied.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard - Star Garage Scheduler</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      color: #000;
    }

    .overlay {
      background-color: rgba(255, 255, 255, 0.9);
      min-height: 100vh;
      padding-top: 30px;
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

    .dashboard-box {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      max-width: 600px;
      margin: 60px auto;
    }

    .dashboard-box h2 {
      font-weight: bold;
    }

    .btn-custom {
      padding: 12px 25px;
      font-size: 1rem;
    }
  </style>
</head>
<body>
  <div class="overlay">

   <a href="index.php" class="btn btn-outline-secondary back-btn">← Back to Home</a>
<a href="logout.php" class="btn btn-outline-danger logout-btn">Logout</a>

    <div class="dashboard-box text-center">
      <h2>Welcome, Client!</h2>
      <p class="alert alert-info mt-3">You can book appointments for your vehicle and view your service history.</p>

      <div class="d-grid gap-3 mt-4">
        <a href="book-appointment.php" class="btn btn-primary btn-custom">Book Appointment</a>
        <a href="my-appointments.php" class="btn btn-info btn-custom">My Appointments</a>
        <a href="change-password.php" class="btn btn-outline-dark btn-custom">Change Password</a>
      </div>
    </div>
  </div>
</body>
</html>
