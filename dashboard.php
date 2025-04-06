<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to database
$host = 'localhost';
$user = 'root';
$pass = 'mary';
$dbname = 'crime3';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];

// Fetch counts
$pendingCount = 0;
$resolvedCount = 0;
$totalCount = 0;

$sqlPending = "SELECT COUNT(*) as count FROM crime_reports WHERE user_id = ? AND status = 'pending'";
$sqlResolved = "SELECT COUNT(*) as count FROM crime_reports WHERE user_id = ? AND status = 'resolved'";
$sqlTotal = "SELECT COUNT(*) as count FROM crime_reports WHERE user_id = ?";

$stmt = $conn->prepare($sqlPending);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($pendingCount);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare($sqlResolved);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($resolvedCount);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare($sqlTotal);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalCount);
$stmt->fetch();
$stmt->close();

// Fetch recent reports (limit 5)
$recentReports = [];
$sqlRecent = "SELECT id, type, location, status, created_at FROM crime_reports WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sqlRecent);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_object()) {
    $recentReports[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="header">
    <div class="header-content">
        <div class="site-title">Crime Reporting System</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="report-crime.php">Report Crime</a>
            <a href="my-reports.php">My Reports</a>
            <a href="profile.php">My Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="welcome-message">
        <h2>Welcome to Your Dashboard</h2>
        <div class="subtitle">Report the Unseen, Secure the Future!</div>
    </div>

    <div class="stats">
        <div class="card">
            <span class="count" style="color: #6666cc;"><?php echo $pendingCount ?? 0; ?></span>
            <span class="label">Pending Reports</span>
        </div>
        <div class="card">
            <span class="count" style="color: #28a745;"><?php echo $resolvedCount ?? 0; ?></span>
            <span class="label">Resolved Reports</span>
        </div>
        <div class="card">
            <span class="count" style="color: #cc66cc;"><?php echo $totalCount ?? 0; ?></span>
            <span class="label">Total Reports</span>
        </div>
    </div>

    <div class="grid-2">
        <div class="panel">
            <h3>Quick Actions</h3>
            <a href="report-crime.php" class="btn btn-primary">Report a New Crime</a>
            <a href="my-reports.php" class="btn btn-secondary">View Your Reports</a>
            <a href="profile.php" class="btn btn-secondary">Manage Your Profile</a>
        </div>

        <div class="panel tips">
            <h3>Safety Tips</h3>
            <ul>
                <li>Always provide accurate and detailed information when reporting.</li>
                <li>Report incidents as soon as possible for effective response.</li>
                <li>Include photos or evidence when available.</li>
                <li>Keep your contact information updated for follow-ups.</li>
                <li>If it's an emergency, call emergency services immediately.</li>
            </ul>
        </div>
    </div>

    <div class="table-container">
        <h3>Recent Reports</h3>

        <?php if (isset($recentReports) && count($recentReports) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Report Type</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentReports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report->type); ?></td>
                            <td><?php echo htmlspecialchars($report->location); ?></td>
                            <td><?php echo date('M d, Y', strtotime($report->created_at)); ?></td>
                            <td>
                                <?php if ($report->status === 'pending'): ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-resolved">Resolved</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view-report.php?id=<?php echo $report->id; ?>" class="view-link">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-reports">
                No recent reports. <a href="report-crime.php" class="view-link">Submit your first report</a>.
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>