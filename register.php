<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

$message = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Validation (basic)
    if (empty($username) || empty($email) || empty($phone) || empty($password) || empty($role)) {
        $message = "Please fill in all fields.";
        $type = "danger";
    } else {
        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $phone, $password, $role);

        if ($stmt->execute()) {
            $message = "✅ Registration successful!";
            $type = "success";
        } else {
            $message = "❌ Error: " . $stmt->error;
            $type = "danger";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('assets/img/garage-bg.png') no-repeat center center/cover;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .form-box {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            margin-top: 60px;
            border-radius: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-box">
                <h3 class="text-center mb-4">📝 Register New User</h3>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $type ?>"><?= $message ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number:</label>
                        <input type="tel" name="phone" id="phone" class="form-control" pattern="[0-9+]+" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Select Role:</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="">-- Select Role --</option>
                            <option value="admin">Admin</option>
                            <option value="technician">Technician</option>
                            <option value="client">Client</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Register</button>
                    <a href="index.php" class="btn btn-secondary mt-2 w-100">← Back to Home</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>

