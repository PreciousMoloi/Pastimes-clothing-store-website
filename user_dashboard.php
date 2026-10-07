<?php
/**
 * user_dashboard.php
 * User dashboard: browse clothes, add to cart with popup showing SellPrice
 * Associative array read from tblClothes
 */

session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'DBConn.php';
$conn = getDBConnection();
$userName = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];

// Fetch user data
$userSQL = "SELECT * FROM tblUser WHERE user_id = $userId";
$userResult = $conn->query($userSQL);
$userData = $userResult->fetch_assoc();

// Fetch all active clothes
$clothesSQL = "SELECT c.*, u.full_name as seller_name FROM tblClothes c LEFT JOIN tblUser u ON c.seller_id = u.user_id WHERE c.status = 'active' ORDER BY c.date_added DESC";
$clothesResult = $conn->query($clothesSQL);

// Handle Add to Cart
$cartMessage = '';
if (isset($_GET['add_to_cart']) && is_numeric($_GET['add_to_cart'])) {
    $itemId = intval($_GET['add_to_cart']);
    $itemSQL = "SELECT * FROM tblClothes WHERE clothes_id = $itemId";
    $itemResult = $conn->query($itemSQL);

    if ($itemResult->num_rows == 1) {
        $item = $itemResult->fetch_assoc();
        $cartMessage = "Added to cart: " . $item['item_name'] . " - R" . number_format($item['price'], 2);

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        $_SESSION['cart'][] = array(
            'id' => $item['clothes_id'],
            'name' => $item['item_name'],
            'price' => $item['price'],
            'image' => $item['image_path']
        );
    }
}

// Get cart count
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - User Dashboard</title>
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header h1 { color: var(--dark); font-size: 24px; }
        .header h1 span { color: var(--primary); }
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .header .welcome {
            color: var(--success);
            font-weight: 600;
            font-size: 14px;
        }
        .header a {
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .header a:hover { color: var(--error); background: #fef2f2; }
        .header .cart-link {
            background: var(--primary);
            color: white !important;
            position: relative;
        }
        .header .cart-link:hover {
            background: var(--primary-dark);
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--error);
            color: white;
            font-size: 11px;
            font-weight: 700;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero {
            background: linear-gradient(135deg, #2dd4bf 0%, #14b8a6 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .hero h2 { font-size: 28px; margin-bottom: 10px; }
        .hero p { font-size: 16px; opacity: 0.9; }
        .main-content {
            padding: 30px;
            max-width: 1200px;
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
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .profile-item {
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
        }
        .profile-item label {
            display: block;
            color: var(--gray);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .profile-item span {
            color: var(--dark);
            font-size: 16px;
            font-weight: 600;
        }
        .cart-message {
            background: #f0fdf4;
            color: var(--success);
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid #bbf7d0;
        }
        .clothes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .clothes-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        .clothes-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .clothes-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 60px;
        }
        .clothes-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .clothes-info { padding: 20px; }
        .clothes-info .item-name {
            font-weight: 700;
            color: var(--dark);
            font-size: 18px;
            margin-bottom: 8px;
        }
        .clothes-info .item-desc {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        .clothes-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 13px;
            color: var(--gray);
        }
        .clothes-meta span {
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .price {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 22px;
        }
        .btn-cart {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        .btn-cart:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 212, 191, 0.3);
        }
        .btn-sell {
            background: var(--dark);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        .btn-sell:hover {
            background: #334155;
            transform: translateY(-2px);
        }
        /* Popup Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal h3 { color: var(--dark); margin-bottom: 15px; }
        .modal .price-display {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin: 20px 0;
        }
        .modal .btn-close {
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
        }
        .modal .btn-close:hover { background: var(--primary-dark); }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pastimes<span>.</span></h1>
        <div class="user-info">
            <span class="welcome">👤 User <?php echo htmlspecialchars($userName); ?> is logged in</span>
            <a href="sell_item.php" class="btn-sell">➕ Sell Item</a>
            <a href="cart.php" class="cart-link">
                🛒 Cart
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="hero">
        <h2>Welcome back, <?php echo htmlspecialchars($userName); ?>!</h2>
        <p>Discover curated secondhand style. Buy and sell unique pieces that deserve a second life.</p>
    </div>

    <div class="main-content">
        <?php if ($cartMessage): ?>
            <div class="cart-message"><?php echo $cartMessage; ?></div>
        <?php endif; ?>

        <!-- User Profile Section -->
        <div class="section">
            <h2>👤 Your Profile</h2>
            <div class="profile-grid">
                <div class="profile-item">
                    <label>Full Name</label>
                    <span><?php echo htmlspecialchars($userData['full_name']); ?></span>
                </div>
                <div class="profile-item">
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($userData['email']); ?></span>
                </div>
                <div class="profile-item">
                    <label>Phone</label>
                    <span><?php echo htmlspecialchars($userData['phone']); ?></span>
                </div>
                <div class="profile-item">
                    <label>Address</label>
                    <span><?php echo htmlspecialchars($userData['address']); ?></span>
                </div>
                <div class="profile-item">
                    <label>Member Since</label>
                    <span><?php echo date('F Y', strtotime($userData['registration_date'])); ?></span>
                </div>
                <div class="profile-item">
                    <label>Status</label>
                    <span style="color: var(--success);">✓ Verified</span>
                </div>
            </div>
        </div>

        <!-- Clothes Listing Section -->
        <div class="section">
            <div class="action-bar">
                <h2>🛍️ Available Items</h2>
                <a href="sell_item.php" class="btn-sell">➕ Sell Your Item</a>
            </div>
            <div class="clothes-grid">
                <?php if ($clothesResult->num_rows > 0): ?>
                    <?php while ($item = $clothesResult->fetch_assoc()): ?>
                        <div class="clothes-card">
                            <div class="clothes-image">
                                <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <?php else: ?>
                                    👕
                                <?php endif; ?>
                            </div>
                            <div class="clothes-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                <div class="item-desc"><?php echo htmlspecialchars($item['description']); ?></div>
                                <div class="clothes-meta">
                                    <span><?php echo htmlspecialchars($item['category']); ?></span>
                                    <span>Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                    <span><?php echo htmlspecialchars($item['color']); ?></span>
                                </div>
                                <div class="price-row">
                                    <div class="price">R<?php echo number_format($item['price'], 2); ?></div>
                                    <a href="?add_to_cart=<?php echo $item['clothes_id']; ?>" 
                                       class="btn-cart"
                                       onclick="showPricePopup('<?php echo htmlspecialchars(addslashes($item['item_name'])); ?>', <?php echo $item['price']; ?>)">
                                        🛒 Add to Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--gray); padding: 40px;">No items available yet. Be the first to <a href="sell_item.php" style="color: var(--primary);">sell something</a>!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Price Popup Modal -->
    <div class="modal-overlay" id="pricePopup">
        <div class="modal">
            <h3>Added to Cart!</h3>
            <p id="popupItemName" style="color: var(--gray);"></p>
            <div class="price-display" id="popupPrice"></div>
            <p style="color: var(--gray); font-size: 14px;">SellPrice shown. Item added to your cart.</p>
            <button class="btn-close" onclick="closePopup()">Continue Shopping</button>
            <br><br>
            <a href="cart.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">View Cart →</a>
        </div>
    </div>

    <script>
        function showPricePopup(itemName, price) {
            document.getElementById('popupItemName').textContent = itemName;
            document.getElementById('popupPrice').textContent = 'R' + price.toFixed(2);
            document.getElementById('pricePopup').classList.add('active');
        }
        function closePopup() {
            document.getElementById('pricePopup').classList.remove('active');
        }
        document.getElementById('pricePopup').addEventListener('click', function(e) {
            if (e.target === this) closePopup();
        });
    </script>
</body>
</html>