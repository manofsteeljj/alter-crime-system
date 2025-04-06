<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if report ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-reports.php");
    exit();
}

$reportId = $_GET['id'];
$userId = $_SESSION['user_id'];

// Connect to database
$host = 'localhost';
$user = 'root';
$pass = 'mary';
$dbname = 'crime3';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete action
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM crime_reports WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reportId, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Report deleted successfully
        header("Location: my-reports.php?deleted=true");
        exit();
    }
    $stmt->close();
}

// Handle status update (for demonstration - normally might need admin rights)
if (isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    if ($newStatus === 'pending' || $newStatus === 'resolved') {
        $stmt = $conn->prepare("UPDATE crime_reports SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $newStatus, $reportId, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $statusUpdateSuccess = true;
        }
        $stmt->close();
    }
}

// Fetch the report details
$report = null;
$sql = "SELECT id, type, location, description, status, created_at, updated_at, evidence_file 
        FROM crime_reports WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $reportId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $report = $result->fetch_object();
} else {
    // Report not found or doesn't belong to user
    header("Location: my-reports.php");
    exit();
}

$stmt->close();

// Fetch comments/updates if you have a related table
$comments = [];
$sql = "SELECT id, comment, created_at, officer_id 
        FROM report_comments WHERE report_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_object()) {
        $comments[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Report | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .report-details {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            border-bottom: 1px solid #eee;
            padding: 12px 0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            width: 30%;
            min-width: 120px;
        }
        .detail-value {
            width: 70%;
            line-height: 1.5;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .comment-box {
            background: #f0f0f0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .comment-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        .evidence-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 5px;
            margin-top: 10px;
        }
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
    </style>
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
    <h2>Report Details</h2>
    
    <?php if (isset($statusUpdateSuccess)): ?>
        <div class="message success">Report status updated successfully.</div>
    <?php endif; ?>
    
    <div class="actions">
        <a href="my-reports.php" class="btn">Back to Reports</a>
        <a href="edit-report.php?id=<?php echo $report->id; ?>" class="btn btn-primary">Edit Report</a>
        <button onclick="confirmDelete()" class="btn btn-danger">Delete Report</button>
    </div>
    
    <div class="report-details">
        <div class="detail-row">
            <div class="detail-label">Report Type:</div>
            <div class="detail-value"><?php echo htmlspecialchars($report->type); ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Location:</div>
            <div class="detail-value"><?php echo htmlspecialchars($report->location); ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value">
                <form method="post" id="statusForm">
                    <select name="status" onchange="document.getElementById('statusForm').submit()">
                        <option value="pending" <?php echo $report->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="resolved" <?php echo $report->status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Date Reported:</div>
            <div class="detail-value"><?php echo date('F d, Y \a\t h:i A', strtotime($report->created_at)); ?></div>
        </div>
        
        <?php if ($report->updated_at): ?>
        <div class="detail-row">
            <div class="detail-label">Last Updated:</div>
            <div class="detail-value"><?php echo date('F d, Y \a\t h:i A', strtotime($report->updated_at)); ?></div>
        </div>
        <?php endif; ?>
        
        <div class="detail-row">
            <div class="detail-label">Description:</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($report->description)); ?></div>
        </div>
        
        <?php if ($report->evidence_file): ?>
        <div class="detail-row">
            <div class="detail-label">Evidence:</div>
            <div class="detail-value">
                <?php
                $fileExt = pathinfo($report->evidence_file, PATHINFO_EXTENSION);
                $imgExts = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array(strtolower($fileExt), $imgExts)):
                ?>
                    <img src="uploads/<?php echo $report->evidence_file; ?>" alt="Evidence" class="evidence-image">
                <?php else: ?>
                    <a href="uploads/<?php echo $report->evidence_file; ?>" target="_blank">View Attached File</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (count($comments) > 0): ?>
    <h3>Updates & Comments</h3>
    <div class="comments-section">
        <?php foreach ($comments as $comment): ?>
            <div class="comment-box">
                <div class="comment-meta">
                    <span>Officer ID: <?php echo $comment->officer_id ? htmlspecialchars($comment->officer_id) : 'System'; ?></span>
                    <span><?php echo date('M d, Y \a\t h:i A', strtotime($comment->created_at)); ?></span>
                </div>
                <div class="comment-text">
                    <?php echo nl2br(htmlspecialchars($comment->comment)); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <form method="post" id="deleteForm">
        <input type="hidden" name="delete" value="1">
    </form>
</div>

<script>
    function confirmDelete() {
        if (confirm("Are you sure you want to delete this report? This action cannot be undone.")) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>

</body>
</html>