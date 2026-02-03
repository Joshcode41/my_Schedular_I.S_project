<?php
$conn = new mysqli("localhost", "root", "", "garage_scheduler_v2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Star Garage Scheduler</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/index_1.jpg') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
      color: white;
      overflow-x: hidden;
    }
    .overlay {
      background-color: rgba(0, 0, 0, 0.75);
      min-height: 100vh;
      padding-top: 80px;
    }
    .nav-tabs .nav-link { color: #fff; }
    .nav-tabs .nav-link.active { background-color: #343a40; }
    .tab-content {
      background: rgba(255,255,255,0.95);
      color: #000;
      border-radius: 10px;
      padding: 30px;
      margin-top: 20px;
    }
    .dashboard-img {
      width: 100%;
      max-width: 500px;
      border-radius: 10px;
      margin: 10px auto;
      display: block;
    }
    .modal-content label { color: #000; }
    .show-password {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
    }
    .password-group { position: relative; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">🌟 Star Garage</a>
    <div class="d-flex">
      <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
      <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
    </div>
  </div>
</nav>

<!-- Hero + Tabs -->
<div class="overlay container">
  <h1 class="display-5 fw-bold text-center mb-4">Welcome to Star Garage Scheduler</h1>
  <p class="lead text-center mb-5">Effortlessly manage your car service appointments with transparency and reliability.</p>

  <ul class="nav nav-tabs justify-content-center" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#goals" type="button">🎯 Aims & Goals</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#feedback" type="button">💬 Feedback</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dashboards" type="button">📊 Dashboards</button>
    </li>
  </ul>

  <!-- Tabs Content -->
  <div class="tab-content mt-4">
    <!-- Goals Tab -->
    <div class="tab-pane fade show active" id="goals">
      <h4 class="fw-bold">Our Mission</h4>
      <p class="mb-4">Star Garage is committed to simplifying garage operations for clients and technicians. We aim to:</p>
      <ul class="fs-5">
        <li class="mb-3">📅 Let customers book appointments online</li>
        <li class="mb-3">🛠️ Allow admins to assign jobs to technicians</li>
        <li class="mb-3">📊 Track service records and completion</li>
        <li class="mb-3">✅ Increase transparency and accountability</li>
      </ul>
    </div>

    <!-- Feedback Tab -->
    <div class="tab-pane fade" id="feedback">
      <h4 class="fw-bold mb-3">Recent Feedback</h4>
      <div class="row">
        <?php
        $feedback_sql = "
          SELECT f.comments, f.rating, f.submitted_at, u.username
          FROM feedback f
          JOIN appointments a ON f.appointment_id = a.id
          JOIN users u ON a.user_id = u.id
          WHERE f.visible_to_tech = 1
          ORDER BY f.submitted_at DESC LIMIT 3
        ";
        $res = $conn->query($feedback_sql);
        if ($res->num_rows > 0):
          while ($fb = $res->fetch_assoc()):
        ?>
        <div class="col-md-4">
          <div class="border rounded p-3 mb-3 bg-light">
            <h6 class="fw-bold"><?= htmlspecialchars($fb['username']) ?> (⭐ <?= $fb['rating'] ?>/5)</h6>
            <p><?= htmlspecialchars($fb['comments']) ?></p>
            <small class="text-muted"><?= date("F j, Y", strtotime($fb['submitted_at'])) ?></small>
          </div>
        </div>
        <?php endwhile; else: ?>
          <p class="text-muted">No feedback available yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Dashboards Tab -->
    <div class="tab-pane fade" id="dashboards">
      <h4 class="fw-bold mb-3">System Dashboards</h4>
      <div class="row text-center">
        <div class="col-md-4">
          <img src="assets/img/index.3.png" class="dashboard-img" alt="Admin Dashboard">
          <p class="fw-bold">Admin Dashboard</p>
        </div>
        <div class="col-md-4">
          <img src="assets/img/index_4.png" class="dashboard-img" alt="Technician Dashboard">
          <p class="fw-bold">Technician Dashboard</p>
        </div>
        <div class="col-md-4">
          <img src="assets/img/index_2.png" class="dashboard-img" alt="Client Dashboard">
          <p class="fw-bold">Client Dashboard</p>
        </div>
      </div>
      <div class="text-center mt-4">
        <img src="assets/img/schedule-demo.png" class="dashboard-img" alt="Schedule Preview">
        <p class="fw-bold">📅 Schedule Preview</p>
      </div>
    </div>
  </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="login.php">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title">🔐 Login</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3 password-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" id="loginPassword" required>
            <span class="show-password" onclick="togglePassword('loginPassword')">👁</span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="register.php">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">📝 Register</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label>Phone</label><input type="tel" name="phone" class="form-control" required></div>
          <div class="mb-3 password-group">
            <label>Password</label>
            <input type="password" name="password" id="regPassword" class="form-control" required>
            <span class="show-password" onclick="togglePassword('regPassword')">👁</span>
          </div>
          <div class="mb-3"><label>Role</label>
            <select name="role" class="form-select" required>
              <option value="client">Client</option>
          
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function togglePassword(id) {
    const input = document.getElementById(id);
    if (input.type === "password") {
      input.type = "text";
    } else {
      input.type = "password";
    }
  }
</script>

</body>
</html>












