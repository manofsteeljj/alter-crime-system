<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = 'mary';
$dbname = 'crime3';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = '';
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            // In a real app, generate a token and send an email here.
            $success = "If this email exists in our system, a password reset link has been sent.";
        } else {
            // For security, don't reveal if email exists.
            $success = "If this email exists in our system, a password reset link has been sent.";
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
    <title>Forgot Password | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .forgot-container, .forgot-container h2, .success, .errors, form, .back-link {
            animation: fadeInUp 0.8s cubic-bezier(.4,2,.6,1) both;
        }
        .forgot-container {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            padding: 32px 28px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(102,102,204,0.07);
        }
        .forgot-container h2 {
            text-align: center;
            color: #23235b;
            margin-bottom: 18px;
        }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 7px; font-weight: 600; color: #555; }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
        }
        .forgot-btn {
            width: 100%;
            padding: 13px;
            background-color: #6666cc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s, transform 0.18s cubic-bezier(.4,2,.6,1);
        }
        .forgot-btn:hover {
            background: #23235b;
            transform: translateY(-3px) scale(1.04);
            box-shadow: 0 4px 16px rgba(102,102,204,0.13);
        }
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
        .back-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #6666cc;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover {
            text-decoration: underline;
            color: #23235b;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 30px 18px 30px;
            background: linear-gradient(135deg, #6666cc, #cc66cc);
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
        }
        .site-title {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
        }
        .nav-links a {
            margin-left: 18px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a.active,
        .nav-links a:hover {
            color: #23235b;
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
            .forgot-container {
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
            <a href="profile.php">My Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</div>

<div class="forgot-container">
    <h2>Forgot Password</h2>
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
    <form method="post" action="forgot-password.php">
        <div class="form-group">
            <label for="email">Enter your email address:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <button type="submit" class="forgot-btn">Send Reset Link</button>
    </form>
    <a href="login.php" class="back-link">&larr; Back to Login</a>
</div>
</body>
</html>