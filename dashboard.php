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

// Fetch user info
$userName = '';
$userEmail = '';
$lastLogin = '';
$stmt = $conn->prepare("SELECT name, username, last_login FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($userName, $userEmail, $lastLogin);
$stmt->fetch();
$stmt->close();

// Fetch report status breakdown
$statusCounts = [
    'pending' => 0,
    'investigating' => 0,
    'resolved' => 0,
    'closed' => 0
];
$sqlStatus = "SELECT status, COUNT(*) as count FROM crime_reports WHERE user_id = ? GROUP BY status";
$stmt = $conn->prepare($sqlStatus);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $statusCounts[$row['status']] = $row['count'];
}
$stmt->close();

// Fetch recent activity (last 5 reports)
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

// Fetch total reports
$totalReports = array_sum($statusCounts);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .dashboard-flex { display: flex; gap: 30px; flex-wrap: wrap; }
        .profile-summary {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 20px 30px;
            min-width: 250px;
            max-width: 300px;
            flex: 1;
        }
        .profile-summary h3 { margin-top: 0; }
        .status-breakdown {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 20px 30px;
            min-width: 250px;
            max-width: 400px;
            flex: 2;
        }
        .status-table { width: 100%; border-collapse: collapse; }
        .status-table th, .status-table td { padding: 8px 12px; }
        .status-table th { background: #f4f6fa; }
        .activity-feed {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 20px 30px;
            margin-top: 30px;
        }
        .quick-links {
            margin: 30px 0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center; /* Center the buttons horizontally */
        }
        .quick-link-btn {
            background: #6666cc;
            color: #fff;
            padding: 12px 22px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .quick-link-btn:hover { background: #23235b; }
        .announcement {
            background: #e3e3ff;
            color: #23235b;
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo img {
            height: 40px;   /* Set your preferred height */
            width: auto;
            max-width: 150px; /* Optional: limit max width */
        }
        .analysis-chart {
            margin-top: 0;
            margin-bottom: 0;
        }
        @media (max-width: 900px) {
            .analysis-chart { margin-top: 20px; }
        }

        /* Fade-in animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .container,
        .dashboard-flex > div,
        .activity-feed,
        .quick-links,
        .announcement {
            animation: fadeInUp 0.8s ease both;
        }
        .dashboard-flex > div {
            animation-delay: 0.2s;
        }
        .analysis-chart {
            animation-delay: 0.4s;
        }
        .activity-feed {
            animation-delay: 0.6s;
        }
        .quick-links {
            animation-delay: 0.8s;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-content">
        <div class="logo">
            <img src="crime-report-logo.png" alt="Crime Report Logo">
            <span class="site-title">Crime Reporting System</span>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="report-crime.php">Report Crime</a>
            <a href="my-reports.php">My Reports</a>
            <a href="profile.php">My Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</div>

<div class="container">

    <div class="announcement">
        <strong>Announcement:</strong> Please ensure your reports are as detailed as possible. For emergencies, call 911.
    </div>

    <div class="dashboard-flex">
        <div class="profile-summary">
            <h3>Your Profile</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
            <p><strong>Last Login:</strong> <?php echo $lastLogin ? htmlspecialchars(date('M d, Y H:i', strtotime($lastLogin))) : 'N/A'; ?></p>
            <a href="profile.php" class="quick-link-btn" style="background:#cc66cc;">Edit Profile</a>
        </div>
        <div class="status-breakdown">
            <h3>Report Status Breakdown</h3>
            <table class="status-table">
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                </tr>
                <tr>
                    <td>Pending</td>
                    <td><?php echo $statusCounts['pending']; ?></td>
                </tr>
                <tr>
                    <td>Investigating</td>
                    <td><?php echo $statusCounts['investigating']; ?></td>
                </tr>
                <tr>
                    <td>Resolved</td>
                    <td><?php echo $statusCounts['resolved']; ?></td>
                </tr>
                <tr>
                    <td>Closed</td>
                    <td><?php echo $statusCounts['closed']; ?></td>
                </tr>
                <tr>
                    <th>Total</th>
                    <th><?php echo $totalReports; ?></th>
                </tr>
            </table>
        </div>
        <div class="analysis-chart" style="background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.04); padding:20px 30px; flex:1; min-width:250px; max-width:400px;">
            <h3>Report Status Analysis</h3>
            <canvas id="statusPieChart" width="300" height="300"></canvas>
        </div>
    </div>

    <div class="quick-links" style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; margin: 30px 0;">
        <a href="report-crime.php" class="quick-link-btn">Report a New Crime</a>
        <a href="my-reports.php" class="quick-link-btn" style="background:#28a745;">View All Reports</a>
        <a href="export-reports.php" class="quick-link-btn" style="background:#ff9800;">Export Reports</a>
    </div>

    <div class="activity-feed">
        <h3>Recent Reports</h3>
        <?php if (count($recentReports) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentReports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report->type); ?></td>
                        <td><?php echo htmlspecialchars($report->location); ?></td>
                        <td><?php echo date('M d, Y', strtotime($report->created_at)); ?></td>
                        <td><?php echo ucfirst($report->status); ?></td>
                        <td><a href="view-report.php?id=<?php echo $report->id; ?>" class="view-link">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-reports">No recent reports. <a href="report-crime.php" class="view-link">Submit your first report</a>.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('statusPieChart').getContext('2d');
const data = {
    labels: ['Pending', 'Investigating', 'Resolved', 'Closed'],
    datasets: [{
        data: [
            <?php echo $statusCounts['pending']; ?>,
            <?php echo $statusCounts['investigating']; ?>,
            <?php echo $statusCounts['resolved']; ?>,
            <?php echo $statusCounts['closed']; ?>
        ],
        backgroundColor: [
            '#6666cc',
            '#ff9800',
            '#28a745',
            '#888'
        ],
        borderWidth: 1
    }]
};
const config = {
    type: 'pie',
    data: data,
    options: {
        responsive: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
};
new Chart(ctx, config);
</script>
</body>
</html>