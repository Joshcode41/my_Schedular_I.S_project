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

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Check if center has appointments
    $check = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE service_center_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        header("Location: manage-centers.php?error=linked");
        exit;
    } else {
        $stmt = $conn->prepare("DELETE FROM service_centers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage-centers.php?success=deleted");
        exit;
    }
}

// Fetch all centers
$centers = [];
$result = $conn->query("SELECT * FROM service_centers");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $centers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Service Centers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: url('assets/img/garage-bg.png') no-repeat center center/cover;">
<div class="container mt-5">
  <div class="bg-white p-5 rounded shadow">
    <h2 class="mb-4">Manage Service Centers</h2>

    <!-- Success & Error Messages -->
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">
        <?php
          if ($_GET['success'] === 'updated') echo "✅ Service center updated successfully!";
          elseif ($_GET['success'] === 'deleted') echo "🗑️ Service center deleted successfully!";
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'linked'): ?>
      <div class="alert alert-danger">
        ⚠️ Cannot delete this service center because it has linked appointments. Please reassign or remove those appointments first.
      </div>
    <?php endif; ?>

    <a href="admin-dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Address</th>
          <th>Phone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($centers) > 0): ?>
          <?php foreach ($centers as $center): ?>
            <tr>
              <td><?= $center['id'] ?></td>
              <td><?= htmlspecialchars($center['name']) ?></td>
              <td><?= htmlspecialchars($center['address']) ?></td>
              <td><?= htmlspecialchars($center['phone']) ?></td>
              <td>
                <a href="edit-center.php?id=<?= $center['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="?delete=<?= $center['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this center?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center">No service centers found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>

