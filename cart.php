<?php
/**
 * cart.php
 * Shopping Cart - view items, remove items, proceed to checkout
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

// Remove item from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $removeIndex = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$removeIndex])) {
        unset($_SESSION['cart'][$removeIndex]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex
    }
    header("Location: cart.php");
    exit();
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = array();
    header("Location: cart.php");
    exit();
}

// Calculate totals
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'];
}
$shipping = count($cartItems) > 0 ? 50.00 : 0.00;
$total = $subtotal + $shipping;

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Shopping Cart</title>
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
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart-table th {
            background: var(--dark);
            color: white;
            padding: 14px;
            text-align: left;
            font-weight: 600;
        }
        .cart-table td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .cart-table tr:hover { background: #f8fafc; }
        .item-image {
            width: 80px;
            height: 80px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            overflow: hidden;
        }
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .item-name { font-weight: 600; color: var(--dark); }
        .price { font-weight: 700; color: var(--primary-dark); font-size: 16px; }
        .btn-remove {
            background: #fef2f2;
            color: var(--error);
            padding: 8px 14px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-remove:hover { background: var(--error); color: white; }
        .summary-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }
        .summary-row.total {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            border-top: 2px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 12px;
        }
        .btn-checkout {
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
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-checkout:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 212, 191, 0.3);
        }
        .btn-continue {
            width: 100%;
            padding: 14px;
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-continue:hover { background: var(--primary); color: white; }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart .icon { font-size: 60px; margin-bottom: 20px; }
        .empty-cart h3 { color: var(--dark); margin-bottom: 10px; }
        .empty-cart p { color: var(--gray); margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pastimes<span>.</span></h1>
        <div>
            <a href="user_dashboard.php">← Back to Shop</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="section">
            <h2>🛒 Your Shopping Cart</h2>

            <?php if (count($cartItems) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $index => $item): ?>
                            <tr>
                                <td>
                                    <div class="item-image">
                                        <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php else: ?>
                                            👕
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                </td>
                                <td class="price">R<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <a href="?remove=<?php echo $index; ?>" class="btn-remove" onclick="return confirm('Remove this item?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 20px; text-align: right;">
                    <a href="?clear=1" style="color: var(--error); font-size: 14px;" onclick="return confirm('Clear entire cart?')">Clear Cart</a>
                </div>

                <div class="summary-box" style="margin-top: 30px;">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>R<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>R<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>R<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
                    <a href="user_dashboard.php" class="btn-continue">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <div class="icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Browse our collection and add items to your cart.</p>
                    <a href="user_dashboard.php" class="btn-checkout" style="max-width: 300px; margin: 0 auto;">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>