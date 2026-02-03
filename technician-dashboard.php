<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) die("Login required.");
$user_id = $_SESSION['user_id'];

// Verify technician role
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== 'technician') die("Access denied.");

// Filtering logic
$where = "technician_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($_GET['service_type'])) {
    $where .= " AND service_type = ?";
    $params[] = $_GET['service_type'];
    $types .= "s";
}
if (!empty($_GET['vehicle_type'])) {
    $where .= " AND vehicle_type = ?";
    $params[] = $_GET['vehicle_type'];
    $types .= "s";
}
if (!empty($_GET['status'])) {
    $where .= " AND status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $where .= " AND preferred_date BETWEEN ? AND ?";
    $params[] = $_GET['from'];
    $params[] = $_GET['to'];
    $types .= "ss";
}

$sql = "SELECT id, vehicle_type, service_type, preferred_date, preferred_time, status, technician_remarks, user_id 
        FROM appointments WHERE $where ORDER BY preferred_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Split active and completed jobs
$active_jobs = [];
$completed_jobs = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'Completed') {
        $completed_jobs[] = $row;
    } else {
        $active_jobs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Technician Dashboard - Star Garage Scheduler</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      color: #000;
    }
    .overlay {
      background-color: rgba(255, 255, 255, 0.92);
      padding: 40px;
      min-height: 100vh;
    }
    .logout-btn {
      position: absolute;
      top: 20px;
      right: 30px;
    }
    .dashboard-title {
      font-weight: bold;
    }
    .filter-form .form-control,
    .filter-form .form-select {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
<div class="overlay">
  <a href="logout.php" class="btn btn-outline-danger logout-btn">Logout</a>
  <div class="container">
    <h2 class="dashboard-title text-center">🔧 Technician Dashboard</h2>
    <p class="alert alert-info text-center">Manage and filter your appointments.</p>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'done'): ?>
      <div class="alert alert-success text-center">✅ Appointment marked as completed and client notified.</div>
    <?php endif; ?>

    <!-- FILTER FORM -->
    <form method="GET" class="row g-2 align-items-end mb-4 filter-form">
      <div class="col-md-2">
        <label>From Date</label>
        <input type="date" name="from" class="form-control" value="<?= $_GET['from'] ?? '' ?>">
      </div>
      <div class="col-md-2">
        <label>To Date</label>
        <input type="date" name="to" class="form-control" value="<?= $_GET['to'] ?? '' ?>">
      </div>
      <div class="col-md-2">
        <label>Service</label>
        <input type="text" name="service_type" class="form-control" placeholder="e.g. Oil Change" value="<?= $_GET['service_type'] ?? '' ?>">
      </div>
      <div class="col-md-2">
        <label>Vehicle</label>
        <input type="text" name="vehicle_type" class="form-control" placeholder="e.g. Toyota" value="<?= $_GET['vehicle_type'] ?? '' ?>">
      </div>
      <div class="col-md-2">
        <label>Status</label>
        <select name="status" class="form-select">
          <option value="">All</option>
          <option <?= (@$_GET['status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
          <option <?= (@$_GET['status'] === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
          <option <?= (@$_GET['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-primary">Filter</button>
      </div>
    </form>

    <!-- ACTIVE APPOINTMENTS -->
    <h4 class="text-primary">📌 Active Appointments</h4>
    <div class="table-responsive mb-5">
      <table class="table table-bordered text-center">
        <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Vehicle</th>
          <th>Service</th>
          <th>Status</th>
          <th>Remarks</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($active_jobs)): ?>
          <tr><td colspan="7" class="text-muted">No active appointments.</td></tr>
        <?php else: ?>
          <?php foreach ($active_jobs as $row): ?>
            <tr>
              <form action="update-appointment.php" method="POST">
                <td><?= $row['preferred_date'] ?></td>
                <td><?= $row['preferred_time'] ?></td>
                <td><?= $row['vehicle_type'] ?></td>
                <td><?= $row['service_type'] ?></td>
                <td>
                  <select name="status" class="form-select form-select-sm">
                    <option <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option <?= $row['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                  </select>
                </td>
                <td>
                  <input type="text" name="remarks" value="<?= htmlspecialchars($row['technician_remarks']) ?>" class="form-control form-control-sm">
                </td>
                <td class="form-inline">
                  <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-primary">Update</button>

                  <form method="POST" action="mark-done.php" class="mt-1">
                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-success mt-1">Mark as Done</button>
                  </form>
                </td>
              </form>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- COMPLETED APPOINTMENTS -->
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h4 class="text-success">✅ Completed Appointments</h4>
      <?php if (!empty($completed_jobs)): ?>
        <div>
          <a href="export-completed.php?type=pdf" class="btn btn-sm btn-outline-danger">📄 Export PDF</a>
          <a href="export-completed.php?type=excel" class="btn btn-sm btn-outline-success">📊 Export Excel</a>
        </div>
      <?php endif; ?>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered text-center">
        <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Vehicle</th>
          <th>Service</th>
          <th>Status</th>
          <th>Remarks</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($completed_jobs)): ?>
          <tr><td colspan="6" class="text-muted">No completed appointments.</td></tr>
        <?php else: ?>
          <?php foreach ($completed_jobs as $row): ?>
            <tr>
              <td><?= $row['preferred_date'] ?></td>
              <td><?= $row['preferred_time'] ?></td>
              <td><?= $row['vehicle_type'] ?></td>
              <td><?= $row['service_type'] ?></td>
              <td><?= $row['status'] ?></td>
              <td><?= nl2br(htmlspecialchars($row['technician_remarks'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
</body>
</html>








