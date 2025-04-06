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

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reportId = $_GET['delete'];
    
    // Make sure the report belongs to the user
    $stmt = $conn->prepare("DELETE FROM crime_reports WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reportId, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $deleteMessage = "Report deleted successfully.";
    } else {
        $deleteError = "Unable to delete report or report doesn't exist.";
    }
    $stmt->close();
}

// Fetch all reports for this user
$reports = [];
$sql = "SELECT id, type, location, status, description, created_at FROM crime_reports WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_object()) {
    $reports[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reports | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .search-filter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        .search-filter input, .search-filter select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .table-actions {
            display: flex;
            gap: 8px;
        }
        .delete-btn {
            color: #dc3545;
            text-decoration: none;
        }
        .edit-btn {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-content">
        <div class="site-title">Crime Reporting System</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="report-crime.php">Report Crime</a>
            <a href="my-reports.php" class="active">My Reports</a>
            <a href="profile.php">My Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <h2>My Crime Reports</h2>
    
    <?php if (isset($deleteMessage)): ?>
        <div class="message success"><?php echo $deleteMessage; ?></div>
    <?php endif; ?>
    
    <?php if (isset($deleteError)): ?>
        <div class="message error"><?php echo $deleteError; ?></div>
    <?php endif; ?>
    
    <div class="search-filter">
        <input type="text" id="searchInput" placeholder="Search reports..." onkeyup="filterReports()">
        <select id="statusFilter" onchange="filterReports()">
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="resolved">Resolved</option>
        </select>
    </div>

    <?php if (count($reports) > 0): ?>
        <div class="table-container">
            <table id="reportsTable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Date Reported</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr class="report-row" data-status="<?php echo $report->status; ?>">
                            <td><?php echo htmlspecialchars($report->type); ?></td>
                            <td><?php echo htmlspecialchars($report->location); ?></td>
                            <td>
                                <?php if ($report->status === 'pending'): ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-resolved">Resolved</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($report->created_at)); ?></td>
                            <td class="table-actions">
                                <a href="view-report.php?id=<?php echo $report->id; ?>" class="view-link">View</a>
                                <a href="edit-report.php?id=<?php echo $report->id; ?>" class="edit-btn">Edit</a>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $report->id; ?>)" class="delete-btn">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-reports">
            You haven't submitted any reports yet. <a href="report-crime.php" class="view-link">Submit your first report</a>.
        </div>
    <?php endif; ?>
</div>

<script>
    function confirmDelete(reportId) {
        if (confirm("Are you sure you want to delete this report? This action cannot be undone.")) {
            window.location.href = "my-reports.php?delete=" + reportId;
        }
    }
    
    function filterReports() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const rows = document.getElementsByClassName('report-row');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const textContent = row.textContent.toLowerCase();
            const status = row.getAttribute('data-status');
            
            const matchesSearch = textContent.includes(searchInput);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            
            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        }
    }
</script>

</body>
</html>