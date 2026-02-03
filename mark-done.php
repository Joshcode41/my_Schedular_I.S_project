<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $remarks = trim($_POST['remarks']);

    // Check if appointment exists and belongs to the technician
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND technician_id = ?");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Insert into completed_appointments table
        $insert = $conn->prepare("INSERT INTO completed_appointments (appointment_id, user_id, technician_id, service_type, vehicle_type, preferred_date, preferred_time, technician_remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param(
            "iiisssss",
            $row['id'],
            $row['user_id'],
            $row['technician_id'],
            $row['service_type'],
            $row['vehicle_type'],
            $row['preferred_date'],
            $row['preferred_time'],
            $remarks
        );
        $insert->execute();

        // Update status to completed
        $update = $conn->prepare("UPDATE appointments SET status = 'Completed', technician_remarks = ? WHERE id = ?");
        $update->bind_param("si", $remarks, $appointment_id);
        $update->execute();

        // Optional: Notify client (e.g., via logs or email)

        header("Location: technician-dashboard.php?msg=done");
        exit;
    } else {
        echo "Invalid appointment or unauthorized access.";
    }
} else {
    echo "Invalid request.";
}
?>




