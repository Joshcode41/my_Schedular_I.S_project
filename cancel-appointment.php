<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
    $user_id = $_SESSION['user_id'];

    // Fetch appointment to verify user ownership and get technician info
    $stmt = $conn->prepare("SELECT technician_id FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        die("Invalid appointment or permission denied.");
    }

    $stmt->bind_result($technician_id);
    $stmt->fetch();

    // Get technician email if available
    $tech_email = null;
    if (!empty($technician_id)) {
        $tech_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $tech_stmt->bind_param("i", $technician_id);
        $tech_stmt->execute();
        $tech_stmt->bind_result($tech_email);
        $tech_stmt->fetch();
        $tech_stmt->close();
    }

    // Delete the appointment
    $delete_stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $appointment_id, $user_id);
    $delete_success = $delete_stmt->execute();

    if ($delete_success) {
        // Optional: Notify technician via email
        if ($tech_email) {
            $subject = "Appointment Cancelled";
            $message = "An appointment assigned to you has been cancelled by the customer.";
            $headers = "From: no-reply@garage-system.com";

            // Uncomment below line if mail is configured
            // mail($tech_email, $subject, $message, $headers);
        }

        header("Location: appointments.php?msg=cancelled");
        exit;
    } else {
        echo "Error deleting appointment.";
    }
} else {
    echo "Invalid request.";
}
?>
