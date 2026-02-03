<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Appointment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
    }
    .overlay {
      background: rgba(255, 255, 255, 0.9);
      padding: 60px;
      margin-top: 40px;
      border-radius: 12px;
    }
    .btn-custom {
      padding: 10px 25px;
    }
  </style>
</head>
<body>
  <div class="container overlay">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a href="client-dashboard.php" class="btn btn-sm btn-outline-secondary">← Back</a>
      <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>

    <h2 class="text-center mb-4">📅 Book an Appointment</h2>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success text-center">
        ✅ Your appointment has been successfully booked!
      </div>
    <?php endif; ?>

    <form action="booking.php" method="POST">
      <div class="mb-3">
        <label for="vehicle" class="form-label">Vehicle Type</label>
        <input type="text" name="vehicle" id="vehicle" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="service" class="form-label">Service Type</label>
        <select name="service" id="service" class="form-control" required>
          <option value="">-- Select Service --</option>
          <option value="Oil Change">Oil Change</option>
          <option value="Brake Repair">Brake Repair</option>
          <option value="Engine Diagnosis">Engine Diagnosis</option>
          <option value="Tyre Repair">Tyre Repair</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="date" class="form-label">Preferred Date</label>
        <input type="date" name="date" id="date" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="time" class="form-label">Preferred Time</label>
        <input type="time" name="time" id="time" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary btn-custom w-100">Submit Booking</button>
    </form>
  </div>
</body>
</html>
