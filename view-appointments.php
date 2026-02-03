<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// ✅ Handle appointment cancellation
if (isset($_GET['cancel'])) {
    $cancel_id = intval($_GET['cancel']);
    $conn->query("UPDATE appointments SET status='cancelled' WHERE id=$cancel_id AND status IN ('pending','ongoing')");
    header("Location: view-appointments.php?canceled=1");
    exit();
}

// ✅ Handle technician assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_tech'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $technician_id = intval($_POST['technician_id']);

    $assign_stmt = $conn->prepare("UPDATE appointments SET technician_id = ?, status = 'ongoing' WHERE id = ?");
    $assign_stmt->bind_param("ii", $technician_id, $appointment_id);
    $assign_stmt->execute();
    $assign_stmt->close();

    header("Location: view-appointments.php?assigned=1");
    exit();
}

// 🔄 Fetch all technicians for dropdown
$tech_result = $conn->query("SELECT id, username FROM users WHERE role = 'technician'");
$technicians = [];
while ($tech = $tech_result->fetch_assoc()) {
    $technicians[] = $tech;
}

// 📋 Fetch ALL appointments
$sqlAll = "SELECT a.id, u.username AS customer, a.service_type, a.vehicle_type, a.preferred_date, a.preferred_time, a.status, t.username AS technician_name
FROM appointments a
JOIN users u ON a.user_id = u.id
LEFT JOIN users t ON a.technician_id = t.id
ORDER BY a.preferred_date, a.preferred_time";
$resultAll = $conn->query($sqlAll);

// 📋 Fetch pending/ongoing
$sqlActive = "SELECT a.id, u.username AS customer, a.service_type, a.vehicle_type, a.preferred_date, a.preferred_time, a.status, t.username AS technician_name
FROM appointments a
JOIN users u ON a.user_id = u.id
LEFT JOIN users t ON a.technician_id = t.id
WHERE a.status IN ('pending','ongoing')
ORDER BY a.preferred_date, a.preferred_time";
$resultActive = $conn->query($sqlActive);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>View Appointments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      color: #000;
    }
    .overlay {
      background: rgba(255,255,255,0.95);
      padding: 50px;
    }
    .table th, .table td {
      vertical-align: middle !important;
    }
  </style>
</head>
<body>
<div class="overlay">
  <a href="admin-dashboard.php" class="btn btn-outline-secondary mb-3">← Back</a>
  <a href="logout.php" class="btn btn-danger mb-3 float-end">Logout</a>

  <?php if (isset($_GET['canceled'])): ?>
    <div class="alert alert-warning text-center">❌ Appointment canceled successfully.</div>
  <?php elseif (isset($_GET['assigned'])): ?>
    <div class="alert alert-success text-center">✅ Technician assigned successfully.</div>
  <?php endif; ?>

  <h2 class="text-center mb-4">📋 All Appointments</h2>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped text-center">
      <thead class="table-dark">
        <tr>
          <th>Customer</th>
          <th>Service</th>
          <th>Vehicle</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
          <th>Technician</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $resultAll->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['customer']) ?></td>
          <td><?= htmlspecialchars($row['service_type']) ?></td>
          <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
          <td><?= htmlspecialchars($row['preferred_date']) ?></td>
          <td><?= htmlspecialchars($row['preferred_time']) ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td><?= htmlspecialchars($row['technician_name'] ?? 'Not Assigned') ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <h3 class="text-center mb-3">⏳ Pending & Ongoing Appointments</h3>
  <p class="alert alert-info text-center">
    You can cancel appointments or assign technicians to pending/ongoing bookings.
  </p>
  <div class="table-responsive">
    <table class="table table-bordered table-striped text-center">
      <thead class="table-warning">
        <tr>
          <th>Customer</th>
          <th>Service</th>
          <th>Vehicle</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
          <th>Technician</th>
          <th>Assign</th>
          <th>Cancel</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $resultActive->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['customer']) ?></td>
          <td><?= htmlspecialchars($row['service_type']) ?></td>
          <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
          <td><?= htmlspecialchars($row['preferred_date']) ?></td>
          <td><?= htmlspecialchars($row['preferred_time']) ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td><?= htmlspecialchars($row['technician_name'] ?? 'Not Assigned') ?></td>
          <td>
            <form method="POST" class="d-flex align-items-center">
              <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
              <select name="technician_id" class="form-select form-select-sm me-2" required>
                <option value="">-- Select --</option>
                <?php foreach ($technicians as $tech): ?>
                  <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['username']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="assign_tech" class="btn btn-sm btn-success">Assign</button>
            </form>
          </td>
          <td>
            <a href="?cancel=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this appointment?')">Cancel</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>






