<?php
// Start session and check user authentication
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Define variables to store form data and errors
$reportData = [
    'type' => '',
    'location' => '',
    'date' => '',
    'time' => '',
    'description' => '',
    'witnesses' => '',
    'evidence' => ''
];
$errors = [];
$success = false;

// Create database connection
$host = 'localhost';
$username = 'root';
$password = 'mary';
$dbname = 'crime3';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Form processing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate crime type
    if (empty($_POST['type'])) {
        $errors['type'] = 'Crime type is required';
    } else {
        $reportData['type'] = htmlspecialchars($_POST['type']);
    }
    
    // Validate location
    if (empty($_POST['location'])) {
        $errors['location'] = 'Location is required';
    } else {
        $reportData['location'] = htmlspecialchars($_POST['location']);
    }
    
    // Validate date
    if (empty($_POST['date'])) {
        $errors['date'] = 'Date is required';
    } else {
        $reportData['date'] = htmlspecialchars($_POST['date']);
    }
    
    // Validate description
    if (empty($_POST['description'])) {
        $errors['description'] = 'Description is required';
    } else if (strlen($_POST['description']) < 20) {
        $errors['description'] = 'Please provide a more detailed description (at least 20 characters)';
    } else {
        $reportData['description'] = htmlspecialchars($_POST['description']);
    }

    // Optional fields
    $reportData['witnesses'] = !empty($_POST['witnesses']) ? htmlspecialchars($_POST['witnesses']) : '';
    $reportData['evidence'] = !empty($_POST['evidence']) ? htmlspecialchars($_POST['evidence']) : '';

    // Process file upload if present
    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] == 0) {
        // Handle file upload logic (optional)
        $fileUploaded = true;
        $fileName = uniqid() . '-' . basename($_FILES['evidence_file']['name']);
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filePath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['evidence_file']['tmp_name'], $filePath);
        $reportData['evidence'] = $filePath; // Save the file path
    } else {
        // If no file is uploaded, set evidence path to NULL
        $reportData['evidence'] = null;
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // If time is empty, set it to NULL
        $incident_time = empty($reportData['time']) ? NULL : $reportData['time'];

        // Prepare SQL query
        $stmt = $conn->prepare("INSERT INTO crime_reports (user_id, type, location, incident_date, incident_time, description, witnesses, evidence_description, evidence_file_path, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        // Bind the parameters to the SQL query
        $stmt->bind_param(
            "issssssss", // Update to bind the correct number of parameters
            $_SESSION['user_id'], // Assuming 'user_id' is stored in the session
            $reportData['type'],
            $reportData['location'],
            $reportData['date'],
            $incident_time, // Bind the incident_time parameter
            $reportData['description'],
            $reportData['witnesses'],
            $reportData['evidence'], // Bind evidence description if necessary
            $reportData['evidence'] // Bind the evidence file path
        );
        
        // Execute the query
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Failed to submit report. Please try again.";
        }
        
        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Crime | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="create.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="header">
    <div class="header-content">
        <div class="logo" style="display: flex; align-items: center; gap: 15px;">
            <img src="crime-report-logo.png" alt="Crime Report Logo" style="height:40px; width:auto; max-width:150px;">
            <span class="site-title">Crime Reporting System</span>
        </div>
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
    <div class="page-title">
        <h2>Report a Crime</h2>
        <div class="subtitle">Help make your community safer by reporting incidents</div>
    </div>

    <div class="form-container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                Your report has been submitted successfully. We'll review it shortly and update you on any developments.
            </div>
        <?php endif; ?>

        <form action="report-crime.php" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Crime Type *</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="" disabled selected>Select crime type</option>
                        <option value="Theft" <?php echo ($reportData['type'] === 'Theft') ? 'selected' : ''; ?>>Theft</option>
                        <option value="Assault" <?php echo ($reportData['type'] === 'Assault') ? 'selected' : ''; ?>>Assault</option>
                        <option value="Vandalism" <?php echo ($reportData['type'] === 'Vandalism') ? 'selected' : ''; ?>>Vandalism</option>
                        <option value="Fraud" <?php echo ($reportData['type'] === 'Fraud') ? 'selected' : ''; ?>>Fraud</option>
                        <option value="Burglary" <?php echo ($reportData['type'] === 'Burglary') ? 'selected' : ''; ?>>Burglary</option>
                        <option value="Drug-related" <?php echo ($reportData['type'] === 'Drug-related') ? 'selected' : ''; ?>>Drug-related</option>
                        <option value="Other" <?php echo ($reportData['type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <?php if (isset($errors['type'])): ?>
                        <div class="error-message"><?php echo $errors['type']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?php echo $reportData['location']; ?>" required placeholder="Enter street address or area">
                    <?php if (isset($errors['location'])): ?>
                        <div class="error-message"><?php echo $errors['location']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date">Date of Incident *</label>
                    <input type="date" id="date" name="date" class="form-control" value="<?php echo $reportData['date']; ?>" required max="<?php echo date('Y-m-d'); ?>">
                    <?php if (isset($errors['date'])): ?>
                        <div class="error-message"><?php echo $errors['date']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="time">Time of Incident (if known)</label>
                    <input type="time" id="time" name="time" class="form-control" value="<?php echo $reportData['time']; ?>">
                    <div class="helper-text">If you're unsure about the exact time, leave this blank</div>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description of Incident *</label>
                <textarea id="description" name="description" class="form-control" required placeholder="Please provide as much detail as possible about what happened"><?php echo $reportData['description']; ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="error-message"><?php echo $errors['description']; ?></div>
                <?php endif; ?>
                <div class="helper-text">Include details like what happened, descriptions of people involved, etc.</div>
            </div>

            <div class="form-group">
                <label for="witnesses">Witnesses (if any)</label>
                <textarea id="witnesses" name="witnesses" class="form-control" placeholder="Names and contact information of any witnesses"><?php echo $reportData['witnesses']; ?></textarea>
            </div>

            <div class="form-group">
                <label for="evidence">Additional Evidence</label>
                <textarea id="evidence" name="evidence" class="form-control" placeholder="Describe any evidence you have (CCTV footage, photos, etc.)"><?php echo $reportData['evidence']; ?></textarea>
                
                <label for="evidence_file" class="file-input">Upload Evidence Files (optional)</label>
                <input type="file" id="evidence_file" name="evidence_file" accept="image/*,.pdf,.doc,.docx">
                <div class="helper-text">Accepted file types: Images, PDF, and Word documents (Max size: 5MB)</div>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Clear Form</button>
                <button type="submit" class="btn btn-primary">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<style>
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
.page-title,
.form-container {
    animation: fadeInUp 0.8s cubic-bezier(.4,2,.6,1) both;
}
.page-title { animation-delay: 0.1s; }
.form-container { animation-delay: 0.2s; }

.btn, .btn-primary, .btn-secondary {
    transition: transform 0.18s cubic-bezier(.4,2,.6,1), box-shadow 0.18s;
}
.btn:hover, .btn-primary:hover, .btn-secondary:hover {
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 4px 16px rgba(102,102,204,0.13);
}

.form-control:focus {
    border-color: #6666cc;
    box-shadow: 0 0 0 2px #e3e3ff;
    transition: border-color 0.2s, box-shadow 0.2s;
}
</style>

</body>
</html>