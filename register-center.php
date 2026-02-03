<!-- register-center.php -->
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

// Form processing
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if ($name && $address && $phone) {
        $stmt = $conn->prepare("INSERT INTO service_centers (name, address, phone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $address, $phone);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Service Center registered successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error registering service center.</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-warning'>All fields are required.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Register Service Center</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: url('assets/img/garage-bg.png') no-repeat center center/cover;">
<div class="container mt-5">
  <div class="bg-white p-5 rounded shadow" style="max-width: 600px; margin: auto;">
    <h2 class="mb-4">Register a New Service Center</h2>
    <?= $message ?>
    <form method="POST">
      <div class="mb-3">
        <label for="name" class="form-label">Center Name</label>
        <input type="text" class="form-control" name="name" required>
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" name="address" required>
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" class="form-control" name="phone" required>
      </div>
      <button type="submit" class="btn btn-primary">Register Center</button>
      <a href="admin-dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </form>
  </div>
</div>
</body>
</html>
