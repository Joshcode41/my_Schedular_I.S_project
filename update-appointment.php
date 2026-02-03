<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) die("Login required.");

$user_id = $_SESSION['user_id'];
$appointment_id = $_POST['appointment_id'] ?? null;
$status = $_POST['status'] ?? '';
$remarks = $_POST['remarks'] ?? '';

$success = false;
$error = '';

// Ensure only assigned technician can update
if ($appointment_id && $status && $remarks) {
    $stmt = $conn->prepare("UPDATE appointments 
                            SET status = ?, technician_remarks = ? 
                            WHERE id = ? AND technician_id = ?");
    $stmt->bind_param("ssii", $status, $remarks, $appointment_id, $user_id);

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = "❌ Error updating appointment: " . $conn->error;
    }
} else {
    $error = "❌ Incomplete form data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Status - Star Garage Scheduler</title>
  <meta http-equiv="refresh" content="<?= $success ? '3;url=technician-dashboard.php' : '' ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
    }

    .overlay {
      background-color: rgba(255, 255, 255, 0.94);
      min-height: 100vh;
      padding: 60px;
    }

    .feedback-box {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      max-width: 600px;
      margin: 0 auto;
      text-align: center;
    }

    .btn-custom {
      margin-top: 20px;
      padding: 10px 25px;
      font-size: 1rem;
    }
  </style>
</head>
<body>
  <div class="overlay">
    <div class="feedback-box">
      <?php if ($success): ?>
        <h3 class="text-success">✅ Appointment updated successfully!</h3>
        <p class="mt-3">Redirecting to dashboard in 3 seconds...</p>
      <?php else: ?>
        <h4 class="text-danger"><?= $error ?></h4>
        <a href="technician-dashboard.php" class="btn btn-outline-primary btn-custom">← Back to Dashboard</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
