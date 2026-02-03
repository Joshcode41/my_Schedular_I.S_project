<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'technician';

    $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Technician registered successfully.";
            header("Location: register-tech.php");
            exit();
        } else {
            die("❌ Failed to register technician: " . $stmt->error);
        }
    } else {
        die("❌ Database error: " . $conn->error);
    }
}
