<?php
/**
 * login.php
 * Main login & registration for Pastimes
 * Features: sticky form, password hash compare, admin verification required
 */

session_start();
require_once 'DBConn.php';

$stickyEmail = '';
$errorMessage = '';
$successMessage = '';

// HANDLE LOGIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $stickyEmail = htmlspecialchars($email);

    if (empty($email) || empty($password)) {
        $errorMessage = "Please fill in all fields.";
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM tblUser WHERE email = ?");
        if ($stmt === false) {
            $errorMessage = "Database tables not found. Please run loadClothingStore.php first.";
        } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $inputHash = md5($password);

            if ($inputHash === $user['password_hash']) {
                if ($user['is_verified'] == 1) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['is_logged_in'] = true;
                    $successMessage = "User " . $user['full_name'] . " is logged in";
                    header("Refresh: 2; URL=user_dashboard.php");
                } else {
                    $errorMessage = "Your account is pending admin verification. Please wait for approval.";
                }
            } else {
                $errorMessage = "Invalid password. Please try again.";
            }
        } else {
            $errorMessage = "User not found. Please register or check your email.";
        }
        $stmt->close();
        }
        closeDBConnection($conn);
    }
}

// HANDLE REGISTRATION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_submit'])) {
    $fullName = trim($_POST['reg_fullname'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $password = trim($_POST['reg_password'] ?? '');
    $address = trim($_POST['reg_address'] ?? '');
    $phone = trim($_POST['reg_phone'] ?? '');

    if (empty($fullName) || empty($email) || empty($password)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif (strlen($password) < 8) {
        $errorMessage = "Password must be at least 8 characters long.";
    } else {
        $conn = getDBConnection();
        $checkStmt = $conn->prepare("SELECT user_id FROM tblUser WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $errorMessage = "Email already registered. Please use a different email or login.";
        } else {
            $passwordHash = md5($password);
            $insertStmt = $conn->prepare("INSERT INTO tblUser (full_name, email, password_hash, address, phone, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
            $insertStmt->bind_param("sssss", $fullName, $email, $passwordHash, $address, $phone);
            if ($insertStmt->execute()) {
                $successMessage = "Registration successful! Your account is pending admin verification.";
            } else {
                $errorMessage = "Registration failed. Please try again.";
            }
            $insertStmt->close();
        }
        $checkStmt->close();
        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Login</title>
    <style>
        :root {
            --primary: #2dd4bf;
            --primary-dark: #14b8a6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --error: #ef4444;
            --success: #22c55e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: var(--dark); font-size: 32px; font-weight: 700; }
        .logo span { color: var(--primary); }
        .logo p { color: var(--gray); margin-top: 5px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px; color: var(--dark);
            font-weight: 600; font-size: 14px;
        }
        .form-group input {
            width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0;
            border-radius: 12px; font-size: 15px; transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.1);
        }
        .btn {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover {
            background: var(--primary-dark); transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 212, 191, 0.3);
        }
        .btn-secondary {
            background: transparent; color: var(--primary);
            border: 2px solid var(--primary); margin-top: 10px;
        }
        .btn-secondary:hover { background: var(--primary); color: white; }
        .message {
            padding: 14px; border-radius: 12px; margin-bottom: 20px;
            font-size: 14px; font-weight: 500;
        }
        .error { background: #fef2f2; color: var(--error); border: 1px solid #fecaca; }
        .success { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }
        .toggle-form {
            text-align: center; margin-top: 20px; color: var(--gray); font-size: 14px;
        }
        .toggle-form a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .toggle-form a:hover { text-decoration: underline; }
        .hidden { display: none; }
        .admin-link { text-align: center; margin-top: 15px; }
        .admin-link a { color: var(--gray); font-size: 13px; text-decoration: none; }
        .admin-link a:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Pastimes<span>.</span></h1>
            <p>Pre-loved fashion, newly adored</p>
        </div>

        <?php if ($errorMessage): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <div id="loginForm">
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo $stickyEmail; ?>" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required minlength="8">
                </div>
                <button type="submit" name="login_submit" class="btn btn-primary">Login</button>
            </form>
            <div class="toggle-form">
                Don't have an account? <a href="#" onclick="toggleForms()">Register here</a>
            </div>
        </div>

        <!-- REGISTRATION FORM -->
        <div id="registerForm" class="hidden">
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="reg_fullname">Full Name *</label>
                    <input type="text" id="reg_fullname" name="reg_fullname" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">Email Address *</label>
                    <input type="email" id="reg_email" name="reg_email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Password * (min 8 characters)</label>
                    <input type="password" id="reg_password" name="reg_password" placeholder="Create a password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="reg_address">Address</label>
                    <input type="text" id="reg_address" name="reg_address" placeholder="Enter your address">
                </div>
                <div class="form-group">
                    <label for="reg_phone">Phone Number</label>
                    <input type="tel" id="reg_phone" name="reg_phone" placeholder="Enter your phone number">
                </div>
                <button type="submit" name="register_submit" class="btn btn-primary">Register</button>
            </form>
            <div class="toggle-form">
                Already have an account? <a href="#" onclick="toggleForms()">Login here</a>
            </div>
        </div>

        <div class="admin-link">
            <a href="admin_login.php">← Admin Login</a>
        </div>
    </div>

    <script>
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            if (loginForm.classList.contains('hidden')) {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>