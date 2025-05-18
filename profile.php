<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = 'mary';
$dbname = 'crime3';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$name = $email = '';
$errors = [];
$success = '';

// Fetch user details
$stmt = $conn->prepare("SELECT name, username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newName = trim($_POST['name']);
    $newEmail = trim($_POST['email']);

    if (empty($newName)) $errors[] = "Name is required.";
    if (empty($newEmail)) $errors[] = "Email is required.";
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newName, $newEmail, $userId);
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            $name = $newName;
            $email = $newEmail;
        } else {
            $errors[] = "Something went wrong. Please try again.";
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
    <title>Profile | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .profile-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
        }
        .register-button {
            width: 100%;
            padding: 15px;
            background-color: #6666cc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .register-button:hover {
            background-color: #5555bb;
        }
        .errors, .success {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        .errors {
            background-color: #ffe6e6;
            color: #d00;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
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
        .profile-container, .profile-container h2, .errors, .success, form, .register-button {
            animation: fadeInUp 0.8s cubic-bezier(.4,2,.6,1) both;
        }
        .profile-container h2 { animation-delay: 0.1s; }
        .errors, .success { animation-delay: 0.2s; }
        form { animation-delay: 0.3s; }
        .register-button { animation-delay: 0.4s; }
    </style>
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
            <a href="profile.php" class="active">My Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="profile-container">
        <h2>My Profile</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <button type="submit" class="register-button">Update Profile</button>
        </form>
        <div style="text-align:center; margin-top:20px;">
            <a href="change-password.php" class="register-button" style="background:#cc66cc; width:auto; display:inline-block;">Change Password</a>
        </div>
    </div>
</div>

</body>
</html>