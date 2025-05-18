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
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Fetch current password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    // Validate current password
    if (empty($current) || empty($new) || empty($confirm)) {
        $errors[] = "All fields are required.";
    } elseif (!password_verify($current, $hashed)) {
        $errors[] = "Current password is incorrect.";
    } elseif (strlen($new) < 8) {
        $errors[] = "New password must be at least 8 characters.";
    } elseif ($new !== $confirm) {
        $errors[] = "New passwords do not match.";
    }

    // Update password
    if (empty($errors)) {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newHash, $userId);
        if ($stmt->execute()) {
            $success = "Password changed successfully.";
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
    <title>Change Password | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
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
        .change-password-container,
        .change-password-container h2,
        .errors,
        .success,
        form,
        .register-button {
            animation: fadeInUp 0.8s cubic-bezier(.4,2,.6,1) both;
        }
        .change-password-container h2 { animation-delay: 0.1s; }
        .errors, .success { animation-delay: 0.2s; }
        form { animation-delay: 0.3s; }
        .register-button { animation-delay: 0.4s; }

        .change-password-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .change-password-container h2 {
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
        input[type="password"] {
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
            transition: background-color 0.3s, transform 0.18s cubic-bezier(.4,2,.6,1), box-shadow 0.18s;
        }
        .register-button:hover {
            background-color: #5555bb;
            transform: translateY(-3px) scale(1.04);
            box-shadow: 0 4px 16px rgba(102,102,204,0.13);
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
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6666cc;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover {
            text-decoration: underline;
            color: #23235b;
        }
        .header {
            background: linear-gradient(135deg, #6666cc, #cc66cc);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo img {
            height: 40px;
            width: auto;
            max-width: 120px;
            transition: transform 0.3s;
        }
        .logo img:hover {
            transform: scale(1.08) rotate(-3deg);
        }
        .site-title {
            font-size: 22px;
            font-weight: 700;
            color: #23235b;
            letter-spacing: 1px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            margin-left: 0;
            transition: background-color 0.3s;
        }
        .nav-links a.active,
        .nav-links a:hover {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .logout-btn {
            background-color: rgba(255,255,255,0.2);
        }
        @media (max-width: 700px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                padding: 12px 10px;
            }
            .logo img {
                height: 32px;
                max-width: 90px;
            }
            .site-title {
                font-size: 16px;
            }
            .change-password-container {
                padding: 15px;
            }
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
            <a href="dashboard.php">Dashboard</a>
            <a href="report-crime.php">Report Crime</a>
            <a href="my-reports.php">My Reports</a>
            <a href="profile.php" class="active">My Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="change-password-container">
        <h2>Change Password</h2>

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

        <form method="POST" action="change-password.php">
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="register-button">Change Password</button>
        </form>
        <a href="profile.php" class="back-link">&larr; Back to Profile</a>
    </div>
</div>

</body>
</html>