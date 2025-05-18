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
$reportId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the report
$stmt = $conn->prepare("SELECT id, type, location, description, status FROM crime_reports WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $reportId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    header("Location: my-reports.php");
    exit();
}
$report = $result->fetch_object();
$stmt->close();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    if (empty($type)) $errors[] = "Type is required.";
    if (empty($location)) $errors[] = "Location is required.";
    if (empty($description) || strlen($description) < 20) $errors[] = "Description must be at least 20 characters.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE crime_reports SET type = ?, location = ?, description = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $type, $location, $description, $status, $reportId, $userId);
        if ($stmt->execute()) {
            $success = "Report updated successfully.";
            // Refresh report data
            $report->type = $type;
            $report->location = $location;
            $report->description = $description;
            $report->status = $status;
        } else {
            $errors[] = "Failed to update report.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Report | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .edit-report-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .edit-report-container h2 {
            text-align: center;
            color: #23235b;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #555;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
        }
        textarea { min-height: 80px; }
        .btn {
            padding: 12px 22px;
            background: #6666cc;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn:hover { background: #23235b; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #6666cc;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }
        .errors, .success {
            padding: 13px;
            margin-bottom: 18px;
            border-radius: 5px;
            font-size: 15px;
        }
        .errors {
            background-color: #ffe6e6;
            color: #d00;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-content">
        <div class="logo">
            <img src="crime-report-logo.png" alt="Crime Report Logo" style="height:40px; width:auto; max-width:150px;">
            <span class="site-title">Crime Reporting System</span>
        </div>
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
    <div class="edit-report-container">
        <h2>Edit Crime Report</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="edit-report.php?id=<?php echo $report->id; ?>">
            <div class="form-group">
                <label for="type">Type of Crime</label>
                <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($report->type); ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($report->location); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($report->description); ?></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="pending" <?php if ($report->status === 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="investigating" <?php if ($report->status === 'investigating') echo 'selected'; ?>>Investigating</option>
                    <option value="resolved" <?php if ($report->status === 'resolved') echo 'selected'; ?>>Resolved</option>
                    <option value="closed" <?php if ($report->status === 'closed') echo 'selected'; ?>>Closed</option>
                </select>
            </div>
            <button type="submit" class="btn">Update Report</button>
        </form>
        <a href="my-reports.php" class="back-link">&larr; Back to My Reports</a>
    </div>
</div>

</body>
</html>