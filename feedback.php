<?php
require 'db.php';
session_start();

// Must be logged in
if (!isset($_SESSION['user_id'])) die("Login required.");

$user_id = $_SESSION['user_id'];
$appointment_id = $_GET['aid'] ?? null;

// Verify this appointment belongs to the user
$stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $appointment_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) die("Invalid access to appointment.");
$stmt->close();

// On form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    $sql = "INSERT INTO feedback (appointment_id, rating, comments, submitted_at)
            VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $appointment_id, $rating, $comments);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Feedback submitted successfully!";
        header("Location: my-appointments.php");
        exit();
    } else {
        echo "❌ Error submitting feedback: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Submit Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('assets/img/garage-bg.png') no-repeat center center/cover;
      font-family: 'Segoe UI', sans-serif;
    }

    .overlay {
      background: rgba(255, 255, 255, 0.95);
      padding: 50px;
      margin-top: 40px;
      border-radius: 12px;
    }

    .btn-custom {
      padding: 8px 20px;
    }
  </style>
</head>
<body>
  <div class="container overlay">
    <div class="d-flex justify-content-between mb-3">
      <a href="my-appointments.php" class="btn btn-outline-secondary btn-sm">← Back</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <h2 class="text-center mb-4">📝 Rate Your Service</h2>
    <form method="POST" class="mx-auto" style="max-width: 600px;">
      <div class="mb-3">
        <label for="rating" class="form-label">Rating (1-5)</label>
        <select name="rating" id="rating" class="form-select" required>
          <option value="">-- Select Rating --</option>
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?> ★</option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="comments" class="form-label">Comments</label>
        <textarea name="comments" id="comments" class="form-control" rows="4" required></textarea>
      </div>

      <button type="submit" class="btn btn-primary btn-custom w-100">Submit Feedback</button>
    </form>
  </div>
</body>
</html>
