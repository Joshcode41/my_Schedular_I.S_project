<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Please login to view your appointments.");
}

$user_id = $_SESSION['user_id'];

$alertMsg = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "cancelled") {
        $alertMsg = "<div class='alert alert-warning text-center'>Appointment cancelled successfully.</div>";
    } elseif ($_GET['msg'] == "done") {
        $alertMsg = "<div class='alert alert-success text-center'>✅ Your appointment has been marked as <strong>Completed</strong>.</div>";
    }
}

$sql = "SELECT a.id, a.service_type, a.vehicle_type, a.preferred_date, a.preferred_time, a.status, a.technician_remarks, 
               u.username AS technician_name
        FROM appointments a
        LEFT JOIN users u ON a.technician_id = u.id
        WHERE a.user_id = ?
        ORDER BY a.preferred_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$activeAppointments = [];
$completedAppointments = [];

while ($row = $result->fetch_assoc()) {
    if (strtolower($row['status']) === 'completed') {
        $completedAppointments[] = $row;
    } else {
        $activeAppointments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>My Appointments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
    }
    .overlay {
      background: rgba(255, 255, 255, 0.95);
      padding: 60px;
      margin-top: 40px;
      border-radius: 12px;
    }
    .btn-custom {
      padding: 6px 18px;
    }
  </style>
</head>
<body>
  <div class="container overlay">
    <div class="d-flex justify-content-between mb-3">
      <a href="client-dashboard.php" class="btn btn-outline-secondary btn-sm">← Back</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <h2 class="text-center mb-3">🧾 My Appointment History</h2>

    <?= $alertMsg ?>

   

    <?php if (count($activeAppointments)): ?>
      <h5 class="text-primary mt-4">📌 Active Appointments</h5>
      <input type="text" class="form-control mb-2" placeholder="Filter by service type or date..." id="searchInput">
      <table class="table table-striped table-bordered align-middle text-center mt-2" id="activeTable">
        <thead class="table-light">
          <tr>
            <th>Service</th>
            <th>Vehicle</th>
            <th>Date</th>
            <th>Time</th>
            <th>Technician</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Feedback</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($activeAppointments as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['service_type']) ?></td>
              <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
              <td><?= htmlspecialchars($row['preferred_date']) ?></td>
              <td><?= htmlspecialchars($row['preferred_time']) ?></td>
              <td><?= htmlspecialchars($row['technician_name'] ?? 'Not Assigned') ?></td>
              <td><span class="badge bg-info"><?= ucfirst($row['status']) ?></span></td>
              <td><?= htmlspecialchars($row['technician_remarks'] ?? '-') ?></td>
              <td><span class="text-muted">Unavailable</span></td>
              <td>
                <?php if (strtolower($row['status']) == 'pending'): ?>
                  <form method="POST" action="cancel-appointment.php" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">N/A</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if (count($completedAppointments)): ?>
      <h5 class="text-success mt-5">✅ Completed Appointments</h5>
      <table class="table table-bordered align-middle table-striped text-center mt-2">
        <thead class="table-success">
          <tr>
            <th>Service</th>
            <th>Vehicle</th>
            <th>Date</th>
            <th>Time</th>
            <th>Technician</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Feedback</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($completedAppointments as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['service_type']) ?></td>
              <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
              <td><?= htmlspecialchars($row['preferred_date']) ?></td>
              <td><?= htmlspecialchars($row['preferred_time']) ?></td>
              <td><?= htmlspecialchars($row['technician_name'] ?? 'Not Assigned') ?></td>
              <td><span class="badge bg-success">Completed</span></td>
              <td><?= htmlspecialchars($row['technician_remarks'] ?? '-') ?></td>
              <td>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-id="<?= $row['id'] ?>">Give Feedback</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Feedback Modal -->
  <div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="feedback.php" method="GET">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Submit Feedback</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="aid" id="feedbackAppointmentId">
            <p>You're about to give feedback for a completed service.</p>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Continue</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const feedbackModal = document.getElementById('feedbackModal');
    feedbackModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      const appointmentId = button.getAttribute('data-id');
      document.getElementById('feedbackAppointmentId').value = appointmentId;
    });

    document.getElementById('searchInput').addEventListener('keyup', function () {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#activeTable tbody tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });
  </script>
</body>
</html>

