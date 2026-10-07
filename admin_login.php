<?php
/**
 * admin_login.php
 * Administrator login page for Pastimes Clothing Store
 * Features:
 * - Admin email must contain @pastimes to login (staff verification)
 * - Password compared against hashed value in tblAdmin
 * - Admin registration available if no admins exist
 * - Redirects to admin dashboard on success
 */

// Start session
session_start();

// Include database connection
require_once 'DBConn.php';

// Initialize variables
$stickyEmail = '';
$errorMessage = '';
$successMessage = '';

// Check if any admin exists
$conn = getDBConnection();
$checkAdmin = $conn->query("SELECT COUNT(*) as count FROM tblAdmin");

// Handle case where table doesn't exist yet
if ($checkAdmin === false) {
    $errorMessage = "Database tables not found. Please run loadClothingStore.php first to create the database.";
    $adminCount = 0;
} else {
    $adminCount = $checkAdmin->fetch_assoc()['count'];
}
closeDBConnection($conn);

// ============================================
// HANDLE ADMIN REGISTRATION (Only if no admins exist)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_register'])) {
    $email = trim($_POST['reg_email'] ?? '');
    $password = trim($_POST['reg_password'] ?? '');
    $role = trim($_POST['reg_role'] ?? 'Staff');

    // Validate email contains @pastimes
    if (strpos($email, '@pastimes') === false) {
        $errorMessage = "Admin email must contain '@pastimes' to register as staff.";
    } elseif (strlen($password) < 8) {
        $errorMessage = "Password must be at least 8 characters long.";
    } else {
        $conn = getDBConnection();

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT admin_id FROM tblAdmin WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $errorMessage = "Email already registered.";
        } else {
            $passwordHash = md5($password);
            $insertStmt = $conn->prepare("INSERT INTO tblAdmin (email, password_hash, role) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $email, $passwordHash, $role);

            if ($insertStmt->execute()) {
                $successMessage = "Admin registered successfully! You can now login.";
            } else {
                $errorMessage = "Registration failed: " . $conn->error;
            }
            $insertStmt->close();
        }
        $checkStmt->close();
        closeDBConnection($conn);
    }
}

// ============================================
// HANDLE ADMIN LOGIN
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['admin_email'] ?? '');
    $password = trim($_POST['admin_password'] ?? '');
    $stickyEmail = htmlspecialchars($email);

    if (empty($email) || empty($password)) {
        $errorMessage = "Please enter both email and password.";
    } elseif (strpos($email, '@pastimes') === false) {
        $errorMessage = "Invalid admin email. Staff emails must contain '@pastimes'.";
    } else {
        $conn = getDBConnection();

        // Verify admin credentials against tblAdmin
        $stmt = $conn->prepare("SELECT * FROM tblAdmin WHERE email = ?");
        if ($stmt === false) {
            $errorMessage = "Database error. Please ensure tables are created by running loadClothingStore.php.";
        } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            $inputHash = md5($password);

            if ($inputHash === $admin['password_hash']) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['is_admin'] = true;

                $successMessage = "Administrator " . $admin['role'] . " logged in successfully!";
                header("Refresh: 2; URL=admin_dashboard.php");
            } else {
                $errorMessage = "Invalid admin password. Access denied.";
            }
        } else {
            $errorMessage = "Admin account not found. Please register first or check your credentials.";
        }

        $stmt->close();
        }
        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Admin Login</title>
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
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .admin-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: var(--dark); font-size: 28px; font-weight: 700; }
        .logo span { color: var(--primary); }
        .logo .badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px; color: var(--dark);
            font-weight: 600; font-size: 14px;
        }
        .form-group input, .form-group select {
            width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0;
            border-radius: 12px; font-size: 15px; transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
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
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: var(--gray); font-size: 14px; text-decoration: none; }
        .back-link a:hover { color: var(--primary); }
        .hidden { display: none; }
        .info-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--success);
        }
        .toggle-form {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
            font-size: 14px;
        }
        .toggle-form a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .toggle-form a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="logo">
            <h1>Pastimes<span>.</span></h1>
            <span class="badge">ADMIN PORTAL</span>
        </div>

        <?php if ($errorMessage): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <!-- Info box for first admin setup -->
        <?php if ($adminCount == 0): ?>
            <div class="info-box">
                <strong>First time setup:</strong> No admin accounts found. Please register the first administrator below.
            </div>
        <?php else: ?>
            <div class="info-box" style="background: #dbeafe; border-color: #3b82f6; color: #1e40af;">
                <strong>Staff Registration:</strong> New staff members can register below. Email must contain @pastimes.
            </div>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <div id="loginForm">
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="admin_email">Admin Email (@pastimes required)</label>
                    <input type="email" id="admin_email" name="admin_email" 
                           value="<?php echo $stickyEmail; ?>"
                           placeholder="staff@pastimes.co.za" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="admin_password" 
                           placeholder="Enter admin password" required>
                </div>
                <button type="submit" name="admin_login" class="btn btn-primary">Admin Login</button>
            </form>

            <div class="toggle-form">
                Need to register staff? <a href="#" onclick="toggleForms()">Register here</a>
            </div>
        </div>

        <!-- REGISTRATION FORM (Always available for staff with @pastimes emails) -->
        <div id="registerForm" class="hidden">
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="reg_email">Staff Email * (@pastimes required)</label>
                    <input type="email" id="reg_email" name="reg_email" 
                           placeholder="staff@pastimes.co.za" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Password * (min 8 characters)</label>
                    <input type="password" id="reg_password" name="reg_password" 
                           placeholder="Create password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="reg_role">Role</label>
                    <select id="reg_role" name="reg_role">
                        <option value="Super Admin">Super Admin</option>
                        <option value="Store Manager">Store Manager</option>
                        <option value="Support Staff">Support Staff</option>
                    </select>
                </div>
                <button type="submit" name="admin_register" class="btn btn-primary">Register Admin</button>
            </form>

            <div class="toggle-form">
                Already have an account? <a href="#" onclick="toggleForms()">Login here</a>
            </div>
        </div>

        <div class="back-link">
            <a href="login.php">← Back to Customer Login</a>
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