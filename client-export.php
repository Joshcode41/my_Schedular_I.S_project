<?php
require 'db.php';
require 'vendor/autoload.php'; // For PhpSpreadsheet and Dompdf

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

session_start();

if (!isset($_GET['type']) || !isset($_GET['user_id'])) {
    die("Invalid request.");
}

$type = $_GET['type'];
$user_id = intval($_GET['user_id']);

// Optional filtering by date or service type
$filter = "";
$params = [$user_id];

if (!empty($_GET['service'])) {
    $filter .= " AND a.service_type LIKE ?";
    $params[] = "%" . $_GET['service'] . "%";
}

if (!empty($_GET['date'])) {
    $filter .= " AND a.preferred_date = ?";
    $params[] = $_GET['date'];
}

$sql = "SELECT a.service_type, a.vehicle_type, a.preferred_date, a.preferred_time,
               u.username AS technician_name, a.technician_remarks
        FROM appointments a
        LEFT JOIN users u ON a.technician_id = u.id
        WHERE a.user_id = ? AND a.status = 'Completed' $filter
        ORDER BY a.preferred_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if ($type === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Completed Appointments");

    $headers = ['Service', 'Vehicle', 'Date', 'Time', 'Technician', 'Remarks'];
    $sheet->fromArray($headers, NULL, 'A1');

    $rowNumber = 2;
    foreach ($data as $row) {
        $sheet->fromArray([
            $row['service_type'],
            $row['vehicle_type'],
            $row['preferred_date'],
            $row['preferred_time'],
            $row['technician_name'],
            $row['technician_remarks']
        ], NULL, 'A' . $rowNumber);
        $rowNumber++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Completed_Appointments.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} elseif ($type === 'pdf') {
    $html = '<h3 style="text-align:center">Completed Appointments</h3>';
    $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
    $html .= '<tr><th>Service</th><th>Vehicle</th><th>Date</th><th>Time</th><th>Technician</th><th>Remarks</th></tr>';

    foreach ($data as $row) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['service_type']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['vehicle_type']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['preferred_date']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['preferred_time']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['technician_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['technician_remarks']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("Completed_Appointments.pdf");
    exit;

} else {
    echo "Invalid export type.";
    exit;
}
