<?php
/**
 * admin_dashboard.php
 * Admin dashboard: verify users, manage users (CRUD), manage orders (update status)
 */

session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'DBConn.php';
$conn = getDBConnection();
$message = '';
$messageType = '';

// HANDLE USER VERIFICATION
if (isset($_GET['verify']) && is_numeric($_GET['verify'])) {
    $userId = intval($_GET['verify']);
    $updateSQL = "UPDATE tblUser SET is_verified = 1 WHERE user_id = $userId";
    if ($conn->query($updateSQL) === TRUE) {
        $message = "User verified successfully!";
        $messageType = "success";
    } else {
        $message = "Error verifying user: " . $conn->error;
        $messageType = "error";
    }
}

// HANDLE USER DELETION
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    $deleteSQL = "DELETE FROM tblUser WHERE user_id = $userId";
    if ($conn->query($deleteSQL) === TRUE) {
        $message = "User deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting user: " . $conn->error;
        $messageType = "error";
    }
}

// HANDLE ORDER STATUS UPDATE
if (isset($_GET['update_order']) && is_numeric($_GET['update_order']) && isset($_GET['status'])) {
    $orderId = intval($_GET['update_order']);
    $status = $conn->real_escape_string($_GET['status']);
    $allowedStatuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    if (in_array($status, $allowedStatuses)) {
        $updateSQL = "UPDATE tblAorder SET status = '$status' WHERE order_id = $orderId";
        if ($conn->query($updateSQL) === TRUE) {
            $message = "Order status updated to '$status'!";
            $messageType = "success";
        } else {
            $message = "Error updating order: " . $conn->error;
            $messageType = "error";
        }
    }
}

// HANDLE ADD USER
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $fullName = $conn->real_escape_string(trim($_POST['full_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = trim($_POST['password']);
    $address = $conn->real_escape_string(trim($_POST['address']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $isVerified = isset($_POST['is_verified']) ? 1 : 0;

    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $messageType = "error";
    } else {
        $passwordHash = md5($password);
        $insertSQL = "INSERT INTO tblUser (full_name, email, password_hash, address, phone, is_verified) VALUES ('$fullName', '$email', '$passwordHash', '$address', '$phone', $isVerified)";
        if ($conn->query($insertSQL) === TRUE) {
            $message = "User added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding user: " . $conn->error;
            $messageType = "error";
        }
    }
}

// HANDLE UPDATE USER
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $userId = intval($_POST['user_id']);
    $fullName = $conn->real_escape_string(trim($_POST['full_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $isVerified = isset($_POST['is_verified']) ? 1 : 0;

    $updateSQL = "UPDATE tblUser SET full_name = '$fullName', email = '$email', address = '$address', phone = '$phone', is_verified = $isVerified WHERE user_id = $userId";
    if ($conn->query($updateSQL) === TRUE) {
        $message = "User updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating user: " . $conn->error;
        $messageType = "error";
    }
}

// Fetch all users
$usersSQL = "SELECT * FROM tblUser ORDER BY registration_date DESC";
$usersResult = $conn->query($usersSQL);

// Fetch all orders with buyer and item details
$ordersSQL = "SELECT o.*, u.full_name as buyer_name, c.item_name, c.image_path 
              FROM tblAorder o 
              JOIN tblUser u ON o.buyer_id = u.user_id 
              JOIN tblClothes c ON o.clothes_id = c.clothes_id 
              ORDER BY o.order_date DESC";
$ordersResult = $conn->query($ordersSQL);

// Count stats
$pendingSQL = "SELECT COUNT(*) as pending FROM tblUser WHERE is_verified = 0";
$pendingResult = $conn->query($pendingSQL);
$pendingCount = $pendingResult->fetch_assoc()['pending'];

$totalSQL = "SELECT COUNT(*) as total FROM tblUser";
$totalResult = $conn->query($totalSQL);
$totalCount = $totalResult->fetch_assoc()['total'];

$orderCountSQL = "SELECT COUNT(*) as total FROM tblAorder";
$orderCountResult = $conn->query($orderCountSQL);
$orderCount = $orderCountResult->fetch_assoc()['total'];

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Admin Dashboard</title>
    <style>
        :root {
            --primary: #2dd4bf;
            --primary-dark: #14b8a6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --error: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }
        .header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { color: var(--dark); font-size: 24px; }
        .header h1 span { color: var(--primary); }
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .header .badge {
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .header a {
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
        }
        .header a:hover { color: var(--error); }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
        }
        .stat-card.pending .number { color: var(--warning); }
        .stat-card.verified .number { color: var(--success); }
        .main-content { padding: 0 30px 30px; }
        .section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .section h2 {
            color: var(--dark);
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
        }
        .message {
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .error { background: #fef2f2; color: var(--error); border: 1px solid #fecaca; }
        .success { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .data-table th {
            background: var(--dark);
            color: white;
            padding: 14px;
            text-align: left;
            font-weight: 600;
        }
        .data-table td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }
        .data-table tr:hover { background: #f8fafc; }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-verified { background: #d1fae5; color: #065f46; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #e0e7ff; color: #3730a3; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            transition: all 0.3s ease;
        }
        .btn-verify { background: var(--success); color: white; }
        .btn-verify:hover { background: #16a34a; }
        .btn-edit { background: #3b82f6; color: white; }
        .btn-edit:hover { background: #2563eb; }
        .btn-delete { background: var(--error); color: white; }
        .btn-delete:hover { background: #dc2626; }
        .btn-add { background: var(--primary); color: white; padding: 12px 24px; font-size: 14px; }
        .btn-add:hover { background: var(--primary-dark); }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-group input:focus { outline: none; border-color: var(--primary); }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input { width: auto; }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .tab {
            padding: 10px 20px;
            border: none;
            background: none;
            color: var(--gray);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .tab.active {
            background: var(--primary);
            color: white;
        }
        .tab:hover:not(.active) {
            background: #f1f5f9;
            color: var(--dark);
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .order-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            background: #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pastimes<span>.</span> Admin</h1>
        <div class="user-info">
            <span class="badge"><?php echo $_SESSION['admin_role']; ?></span>
            <span><?php echo $_SESSION['admin_email']; ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="number"><?php echo $totalCount; ?></div>
        </div>
        <div class="stat-card pending">
            <h3>Pending Verification</h3>
            <div class="number"><?php echo $pendingCount; ?></div>
        </div>
        <div class="stat-card verified">
            <h3>Verified Users</h3>
            <div class="number"><?php echo $totalCount - $pendingCount; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Orders</h3>
            <div class="number"><?php echo $orderCount; ?></div>
        </div>
    </div>

    <div class="main-content">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="showTab('users')">👥 User Management</button>
            <button class="tab" onclick="showTab('orders')">📦 Order Management</button>
            <button class="tab" onclick="showTab('add')">➕ Add New User</button>
        </div>

        <!-- Users Tab -->
        <div id="users" class="tab-content active">
            <div class="section">
                <h2>👥 User Management</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($usersResult->num_rows > 0): ?>
                            <?php while ($user = $usersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <?php if ($user['is_verified'] == 1): ?>
                                            <span class="status-badge status-verified">✓ Verified</span>
                                        <?php else: ?>
                                            <span class="status-badge status-pending">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['registration_date'])); ?></td>
                                    <td>
                                        <?php if ($user['is_verified'] == 0): ?>
                                            <a href="?verify=<?php echo $user['user_id']; ?>" class="btn btn-verify">Verify</a>
                                        <?php endif; ?>
                                        <a href="?edit=<?php echo $user['user_id']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $user['user_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center; padding: 30px; color: var(--gray);">No users found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content">
            <div class="section">
                <h2>📦 Order Management</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Image</th>
                            <th>Item</th>
                            <th>Buyer</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordersResult->num_rows > 0): ?>
                            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td>
                                        <?php if (!empty($order['image_path']) && file_exists($order['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($order['image_path']); ?>" class="order-image" alt="">
                                        <?php else: ?>
                                            <div class="order-image" style="display: flex; align-items: center; justify-content: center;">👕</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                    <td>R<?php echo number_format($order['total_price'], 2); ?></td>
                                    <td><?php echo $order['order_date']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select onchange="updateOrderStatus(<?php echo $order['order_id']; ?>, this.value)" style="padding: 6px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <option value="">Change Status</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Processing">Processing</option>
                                            <option value="Shipped">Shipped</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--gray);">No orders found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Tab -->
        <div id="add" class="tab-content">
            <div class="section">
                <h2>➕ Add New User</h2>
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" required placeholder="Enter full name">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label>Password * (min 8 chars)</label>
                            <input type="password" name="password" required minlength="8" placeholder="Create password">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" placeholder="Enter phone number">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Address</label>
                            <input type="text" name="address" placeholder="Enter address">
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" name="is_verified" id="is_verified" value="1">
                            <label for="is_verified" style="margin: 0;">Verified (can login immediately)</label>
                        </div>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-add" style="margin-top: 15px;">Add User</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        function updateOrderStatus(orderId, status) {
            if (status) {
                window.location.href = '?update_order=' + orderId + '&status=' + status;
            }
        }
    </script>
</body>
</html>