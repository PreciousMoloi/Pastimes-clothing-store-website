<?php
/**
 * checkout.php
 * Checkout process: shipping address, payment method, place order
 */

session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'DBConn.php';
$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
if (count($cartItems) == 0) {
    header("Location: cart.php");
    exit();
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'];
}
$shipping = 50.00;
$total = $subtotal + $shipping;

$errorMessage = '';
$successMessage = '';

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');

    if (empty($shippingAddress) || empty($paymentMethod) || empty($city)) {
        $errorMessage = "Please fill in all required shipping fields.";
    } else {
        $fullAddress = $shippingAddress . ', ' . $city . ', ' . $province . ' ' . $postalCode;
        $orderDate = date('Y-m-d');

        $successCount = 0;
        foreach ($cartItems as $item) {
            $stmt = $conn->prepare("INSERT INTO tblAorder (buyer_id, clothes_id, total_price, order_date, status, shipping_address, payment_method) VALUES (?, ?, ?, ?, 'Pending', ?, ?)");
            $stmt->bind_param("iidsss", $userId, $item['id'], $item['price'], $orderDate, $fullAddress, $paymentMethod);
            if ($stmt->execute()) {
                $successCount++;
                // Mark item as sold
                $updateStmt = $conn->prepare("UPDATE tblClothes SET status = 'sold' WHERE clothes_id = ?");
                $updateStmt->bind_param("i", $item['id']);
                $updateStmt->execute();
                $updateStmt->close();
            }
            $stmt->close();
        }

        if ($successCount > 0) {
            $_SESSION['cart'] = array(); // Clear cart
            $successMessage = "Order placed successfully! $successCount item(s) ordered.";
            header("Refresh: 2; URL=user_dashboard.php");
        } else {
            $errorMessage = "Error placing order. Please try again.";
        }
    }
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Checkout</title>
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
        .header a {
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .header a:hover { color: var(--primary); background: #f0fdf4; }
        .main-content {
            padding: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        @media (max-width: 768px) {
            .checkout-grid { grid-template-columns: 1fr; }
        }
        .section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .section h2 {
            color: var(--dark);
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .payment-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover, .payment-option.selected {
            border-color: var(--primary);
            background: #f0fdf4;
        }
        .payment-option input {
            width: auto;
        }
        .order-summary {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--gray);
        }
        .summary-item .name {
            color: var(--dark);
            font-weight: 500;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            border-top: 2px solid #e2e8f0;
            padding-top: 15px;
            margin-top: 15px;
        }
        .btn-place-order {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-place-order:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 212, 191, 0.3);
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
        .trust-badges {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }
        .trust-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--gray);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pastimes<span>.</span></h1>
        <div>
            <a href="cart.php">← Back to Cart</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <?php if ($errorMessage): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="checkout-grid">
                <!-- Left: Shipping & Payment -->
                <div>
                    <div class="section" style="margin-bottom: 20px;">
                        <h2>📍 Shipping Address</h2>
                        <div class="form-group">
                            <label for="shipping_address">Street Address *</label>
                            <textarea id="shipping_address" name="shipping_address" required placeholder="Enter your street address"></textarea>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" required placeholder="City">
                            </div>
                            <div class="form-group">
                                <label for="province">Province</label>
                                <input type="text" id="province" name="province" placeholder="Province">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" placeholder="Postal Code">
                        </div>
                    </div>

                    <div class="section">
                        <h2>💳 Payment Method</h2>
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Credit Card" required>
                                <span>💳 Credit / Debit Card</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Bank Transfer">
                                <span>🏦 Bank Transfer</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Digital Wallet">
                                <span>📱 Digital Wallet</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div>
                    <div class="section">
                        <h2>📋 Order Summary</h2>
                        <div class="order-summary">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-item">
                                    <span class="name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span>R<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div class="summary-item">
                                <span>Shipping</span>
                                <span>R<?php echo number_format($shipping, 2); ?></span>
                            </div>
                            <div class="summary-total">
                                <span>Total</span>
                                <span>R<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                        <button type="submit" name="place_order" class="btn-place-order">Place Order</button>
                        <div class="trust-badges">
                            <div class="trust-badge">🔒 SSL Secure</div>
                            <div class="trust-badge">✓ Verified</div>
                            <div class="trust-badge">🛡️ Buyer Protection</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>