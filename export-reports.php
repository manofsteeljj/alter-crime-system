<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = 'mary';
$dbname = 'crime3';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];

// Fetch all reports for this user
$stmt = $conn->prepare("SELECT id, type, location, incident_date, incident_time, description, witnesses, evidence_description, status, created_at FROM crime_reports WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=crime_reports.csv');

// Output CSV column headers
$output = fopen('php://output', 'w');
fputcsv($output, [
    'Report ID', 'Type', 'Location', 'Incident Date', 'Incident Time', 'Description',
    'Witnesses', 'Evidence Description', 'Status', 'Reported At'
]);

// Output each report row
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['type'],
        $row['location'],
        $row['incident_date'],
        $row['incident_time'],
        $row['description'],
        $row['witnesses'],
        $row['evidence_description'],
        ucfirst($row['status']),
        $row['created_at']
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
exit;