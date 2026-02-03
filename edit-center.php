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

if (!isset($_GET['id'])) die("No center ID provided.");
$id = intval($_GET['id']);

// Fetch current data
$stmt = $conn->prepare("SELECT name, address, phone FROM service_centers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($name, $address, $phone);
$stmt->fetch();
$stmt->close();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['name']);
    $new_address = trim($_POST['address']);
    $new_phone = trim($_POST['phone']);

    if ($new_name && $new_address && $new_phone) {
        $stmt = $conn->prepare("UPDATE service_centers SET name = ?, address = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $new_name, $new_address, $new_phone, $id);
        if ($stmt->execute()) {
            $stmt->close();
            // Redirect with success message
            header("Location: manage-centers.php?success=updated");
            exit;
        } else {
            $error = "Error updating record.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Service Center</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: url('assets/img/garage-bg.png') no-repeat center center/cover;">
<div class="container mt-5">
  <div class="bg-white p-5 rounded shadow" style="max-width: 600px; margin: auto;">
    <h2 class="mb-4">Edit Service Center</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label for="name" class="form-label">Center Name</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($name) ?>" required>
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address) ?>" required>
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
      </div>
      <button type="submit" class="btn btn-success">Update Center</button>
      <a href="manage-centers.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
</body>
</html>

