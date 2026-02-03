<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to book an appointment.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $vehicle = $_POST['vehicle'];
    $service = $_POST['service'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    $sql = "INSERT INTO appointments (user_id, service_type, vehicle_type, preferred_date, preferred_time)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $service, $vehicle, $date, $time);

    if ($stmt->execute()) {
        // Redirect back to booking form with success flag
        header("Location: book-appointment.php?success=1");
        exit();
    } else {
        // Show raw error if something fails (for development)
        echo "❌ Error: " . $conn->error;
    }
}
?>
