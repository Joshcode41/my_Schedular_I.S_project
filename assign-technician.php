<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = $_POST['appointment_id'];
    $technician_id = $_POST['technician_id'];

    $stmt = $conn->prepare("UPDATE appointments SET technician_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $technician_id, $appointment_id);

    if ($stmt->execute()) {
        header("Location: view-appointments.php");
        exit();
    } else {
        echo "❌ Failed to assign technician: " . $conn->error;
    }
}
?>
