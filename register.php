<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$pass = 'mary';
$dbname = 'crime3';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['password_confirmation'];

    // Validation
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Email is already registered.";
    $stmt->close();

    // Register user if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, username, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful. You can now log in.";
            header("Location: login.php");
            exit();
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
    <title>Register | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        .left-panel {
            width: 60%;
            background: linear-gradient(135deg, #6666cc, #cc66cc);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px;
        }
        .right-panel {
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            overflow-y: auto;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
        }
        .logo img {
            height: 48px;
            width: auto;
            max-width: 150px;
        }
        .site-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .tagline {
            font-size: 48px;
            font-weight: 300;
            line-height: 1.2;
        }
        .tagline .emphasis {
            font-weight: 700;
            font-size: 52px;
        }
        .start-here {
            margin-top: auto;
            padding-bottom: 50px;
        }
        .start-here a {
            color: white;
            text-decoration: none;
            font-size: 24px;
            border-bottom: 1px solid white;
        }
        .welcome-back {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }
        .login-instruction {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #f0f5ff;
            font-size: 16px;
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
            margin: 20px 0;
            transition: transform 0.18s cubic-bezier(.4,2,.6,1), box-shadow 0.18s;
        }
        .register-button:hover {
            transform: translateY(-3px) scale(1.04);
            box-shadow: 0 4px 16px rgba(102,102,204,0.13);
        }
        .login-link {
            text-align: center;
        }
        .login-link a {
            color: #6666cc;
            text-decoration: none;
        }
        .errors {
            background-color: #ffe6e6;
            color: #d00;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .errors p {
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .left-panel, .right-panel {
                width: 100%;
            }
            .left-panel {
                height: 30vh;
            }
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
        .left-panel,
        .right-panel,
        .welcome-back,
        .login-instruction,
        form,
        .errors {
            animation: fadeInUp 0.8s cubic-bezier(.4,2,.6,1) both;
        }
        .left-panel { animation-delay: 0.1s; }
        .right-panel { animation-delay: 0.2s; }
        .welcome-back { animation-delay: 0.3s; }
        .login-instruction { animation-delay: 0.4s; }
        form { animation-delay: 0.5s; }
        .errors { animation-delay: 0.6s; }
    </style>
</head>
<body>

<div class="left-panel">
    <div class="logo" style="display: flex; align-items: center; gap: 15px; margin-bottom: 40px;">
        <img src="crime-report-logo.png" alt="Crime Report Logo" style="height:48px; width:auto; max-width:150px;">
        <span class="site-title" style="font-size:28px; font-weight:700; letter-spacing:1px;">Crime Reporting System</span>
    </div>
    <div class="tagline">
        Report the Unseen,<br>
        Secure the <span class="emphasis">Future!</span>
    </div>
    <div class="start-here">
        <a href="register.php">Your Crime Reporting Starts here!</a>
    </div>
</div>

<div class="right-panel">
    <div class="welcome-back">Create Account</div>
    <div class="login-instruction">Register to start reporting incidents</div>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="register-button">Register</button>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </form>
</div>

</body>
</html>