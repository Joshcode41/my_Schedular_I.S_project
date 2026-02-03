<?php
require 'db.php';

// Simulate technician session (remove this in production)
session_start();
if (!isset($_SESSION['user_id'])) die("Login required.");
$technician_id = $_SESSION['user_id'];

// Validate export type
$type = $_GET['type'] ?? 'pdf';
if (!in_array($type, ['pdf', 'excel'])) {
    die("Invalid export type.");
}

// Fetch completed appointments
$stmt = $conn->prepare("SELECT preferred_date, preferred_time, vehicle_type, service_type, technician_remarks FROM appointments WHERE technician_id = ? AND status = 'Completed' ORDER BY preferred_date DESC");
$stmt->bind_param("i", $technician_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if ($type === 'excel') {
    // Excel Export using PhpSpreadsheet
    require 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Completed Tasks');

    // Header
    $sheet->fromArray(['Date', 'Time', 'Vehicle', 'Service', 'Remarks'], NULL, 'A1');

    // Data
    $rowNum = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), NULL, "A$rowNum");
        $rowNum++;
    }

    // Output Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="completed_tasks.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    // PDF Export using Dompdf
    require 'vendor/autoload.php';
    use Dompdf\Dompdf;

    $html = '<h2>Completed Appointments</h2><table border="1" cellpadding="6" cellspacing="0" width="100%">
             <thead><tr><th>Date</th><th>Time</th><th>Vehicle</th><th>Service</th><th>Remarks</th></tr></thead><tbody>';

    foreach ($data as $row) {
        $html .= '<tr><td>' . htmlspecialchars($row['preferred_date']) . '</td><td>' .
                 htmlspecialchars($row['preferred_time']) . '</td><td>' .
                 htmlspecialchars($row['vehicle_type']) . '</td><td>' .
                 htmlspecialchars($row['service_type']) . '</td><td>' .
                 nl2br(htmlspecialchars($row['technician_remarks'])) . '</td></tr>';
    }

    $html .= '</tbody></table>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("completed_tasks.pdf", ["Attachment" => true]);
    exit;
}
