<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// Handle technician registration from modal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_tech'])) {
    $new_username = trim($_POST['new_username']);
    $new_email = trim($_POST['new_email']);
    $new_phone = trim($_POST['new_phone']);
    $temp_password = trim($_POST['new_password']);
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $new_email);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        echo "<div class='alert alert-danger'>❌ Email already exists!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, role, status, password_changed) VALUES (?, ?, ?, ?, 'technician', 'active', 0)");
        $stmt->bind_param("ssss", $new_username, $new_email, $new_phone, $hashed_password);
        if ($stmt->execute()) {
            $log = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, 'Technician registered')");
            $log->bind_param("i", $_SESSION['user_id']);
            $log->execute();
            echo "<div class='alert alert-success'>✅ Technician registered with temporary password: <strong>$temp_password</strong></div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error occurred while registering technician.</div>";
        }
    }
}

// Handle activate/deactivate
if (isset($_GET['deactivate'])) {
    $id = intval($_GET['deactivate']);
    $conn->query("UPDATE users SET status = 'inactive' WHERE id = $id AND role = 'technician'");
    header("Location: manage-technicians.php");
    exit();
}

if (isset($_GET['activate'])) {
    $id = intval($_GET['activate']);
    $conn->query("UPDATE users SET status = 'active' WHERE id = $id AND role = 'technician'");
    header("Location: manage-technicians.php");
    exit();
}

$filter = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";

$activeTechs = $conn->prepare("SELECT * FROM users WHERE role = 'technician' AND status = 'active' AND (username LIKE ? OR email LIKE ?)");
$activeTechs->bind_param("ss", $filter, $filter);
$activeTechs->execute();
$activeResult = $activeTechs->get_result();

$inactiveTechs = $conn->prepare("SELECT * FROM users WHERE role = 'technician' AND status = 'inactive' AND (username LIKE ? OR email LIKE ?)");
$inactiveTechs->bind_param("ss", $filter, $filter);
$inactiveTechs->execute();
$inactiveResult = $inactiveTechs->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Technicians</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
    }
    .overlay {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 60px;
      min-height: 100vh;
    }
    .back-btn {
      position: absolute;
      top: 20px;
      left: 30px;
    }
    .logout-btn {
      position: absolute;
      top: 20px;
      right: 30px;
    }
    .table th, .table td {
      vertical-align: middle;
    }
  </style>
</head>
<body>
<div class="overlay">
  <a href="admin-dashboard.php" class="btn btn-outline-secondary back-btn">← Back</a>
  <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>

  <div class="container">
    <h2 class="text-center mb-4">👨‍🔧 Manage Technicians</h2>

    <!-- Search Filter -->
    <form class="mb-4 text-end" method="GET">
      <div class="input-group w-50 float-end">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button class="btn btn-outline-primary">Search</button>
      </div>
    </form>

    <!-- Register Technician Button -->
    <div class="mb-4 text-end">
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registerModal">
        ➕ Register New Technician
      </button>
    </div>

    <h4>Active Technicians</h4>
    <div class="table-responsive mb-5">
      <table class="table table-striped table-bordered text-center">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while($row = $activeResult->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td>
                <a href="?deactivate=<?= $row['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Deactivate this technician?')">Deactivate</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <h4>Inactive Technicians</h4>
    <div class="table-responsive">
      <table class="table table-bordered table-striped text-center">
        <thead class="table-secondary">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $j = 1; while($row = $inactiveResult->fetch_assoc()): ?>
            <tr>
              <td><?= $j++ ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td>
                <a href="?activate=<?= $row['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Reactivate this technician?')">Activate</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Register Technician Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="registerModalLabel">➕ Register Technician</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="new_username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="new_email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="new_phone" class="form-control" pattern="[0-9+]{10,15}" required>
            <div class="form-text">Enter a valid phone number (10–15 digits).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Temporary Password</label>
            <input type="text" name="new_password" class="form-control" minlength="6" required>
            <div class="form-text">Provide a temporary password for technician login.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="register_tech" class="btn btn-success w-100">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




