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

$error = '';
$emailOld = '';
$emailErrors = [];
$passwordErrors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $emailOld = $email;

    // Validation
    if (empty($email)) $emailErrors[] = "Email is required.";
    if (empty($password)) {
        $passwordErrors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $passwordErrors[] = "Password must be at least 8 characters.";
    }

    if (empty($emailErrors) && empty($passwordErrors)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                header("Location: dashboard.php");
                exit();
            } else {
                $passwordErrors[] = "Invalid password.";
            }
        } else {
            $emailErrors[] = "User not found.";
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
    <title>Login | Crime Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background: #f4f6fa;
        }
        .left-panel {
            width: 60%;
            background: linear-gradient(135deg, #6666cc, #cc66cc);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px;
            position: relative;
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
            margin-top: 40px;
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
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(80px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .right-panel {
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            background: #fff;
            box-shadow: 0 0 24px rgba(102,102,204,0.06);
            animation: slideInRight 0.8s cubic-bezier(.4,2,.6,1) both;
        }
        .welcome-back {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
            color: #23235b;
            letter-spacing: 1px;
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #f0f5ff;
            font-size: 16px;
        }
        .remember-me {
            margin-bottom: 20px;
        }
        .login-button {
            width: 100%;
            padding: 15px;
            background-color: #6666cc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background 0.2s, transform 0.18s cubic-bezier(.4,2,.6,1);
        }
        .login-button:hover {
            background-color: #23235b;
            transform: translateY(-3px) scale(1.04);
            box-shadow: 0 4px 16px rgba(102,102,204,0.13);
        }
        .forgot-password {
            text-align: center;
            margin-bottom: 20px;
        }
        .forgot-password a {
            color: #6666cc;
            text-decoration: none;
        }
        .register-link {
            text-align: center;
        }
        .register-link a {
            color: #6666cc;
            text-decoration: none;
        }
        .error {
            color: red;
            font-size: 13px;
            margin-top: 5px;
        }
        .status {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }
            .left-panel, .right-panel {
                width: 100%;
                min-width: 0;
            }
            .left-panel {
                height: 30vh;
                padding: 30px 20px;
            }
            .right-panel {
                padding: 30px 20px;
            }
            .tagline {
                font-size: 32px;
            }
            .logo img {
                height: 36px;
            }
            .site-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="left-panel">
    <div>
        <div class="logo">
            <img src="crime-report-logo.png" alt="Crime Report Logo">
            <span class="site-title">Crime Reporting System</span>
        </div>
        <div class="tagline">
            Report the Unseen,<br>
            Secure the <span class="emphasis">Future!</span>
        </div>
    </div>
    <div class="start-here">
        <a href="register.php">Your Crime Reporting Starts here!</a>
    </div>
</div>

<div class="right-panel">
    <div class="welcome-back">Welcome Back</div>
    <div class="login-instruction">Sign in to your account to continue</div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="status"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($emailOld); ?>" placeholder="mary@example.com" required>
            <?php foreach ($emailErrors as $err): ?>
                <div class="error"><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="••••••••••••••••••" required>
            <?php foreach ($passwordErrors as $err): ?>
                <div class="error"><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>

        <div class="remember-me">
            <label><input type="checkbox" name="remember"> Remember me</label>
        </div>

        <button type="submit" class="login-button">Login</button>

        <div class="forgot-password">
            <a href="forgot-password.php">Forgot Password?</a>
        </div>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </form>
</div>

</body>
</html>