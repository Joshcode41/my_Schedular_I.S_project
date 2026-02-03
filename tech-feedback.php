<?php
require 'db.php';
session_start();

// Check role is 'technician'
if (!isset($_SESSION['user_id'])) die("Login required.");

$tech_id = $_SESSION['user_id'];

// Verify technician role
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $tech_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== 'technician') die("Access denied.");

// Get feedback
$sql = "SELECT f.rating, f.comments, f.submitted_at, u.fullname AS customer, a.service_type, a.preferred_date 
        FROM feedback f
        JOIN appointments a ON f.appointment_id = a.id
        JOIN users u ON a.user_id = u.id
        WHERE a.technician_id = ? AND f.visible_to_tech = 1
        ORDER BY f.submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tech_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Your Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <h2>Feedback from Customers</h2>
  <?php if ($result->num_rows == 0): ?>
    <p>No feedback received yet.</p>
  <?php else: ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Service</th>
          <th>Date</th>
          <th>Rating</th>
          <th>Comments</th>
          <th>Submitted</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['customer']) ?></td>
            <td><?= $row['service_type'] ?></td>
            <td><?= $row['preferred_date'] ?></td>
            <td><?= $row['rating'] ?>/5</td>
            <td><?= htmlspecialchars($row['comments']) ?></td>
            <td><?= $row['submitted_at'] ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
