<?php
/**
 * sell_item.php
 * Sell clothing item: upload image, add description, price
 * Image stored in images/ folder, URL saved in database
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

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sell_item'])) {
    $itemName = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (empty($itemName) || empty($description) || $price <= 0) {
        $errorMessage = "Please fill in all required fields with valid values.";
    } else {
        // Handle image upload
        $imagePath = '';
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['item_image']['type'];

            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = 'images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = time() . '_' . basename($_FILES['item_image']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['item_image']['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $errorMessage = "Failed to upload image. Please try again.";
                }
            } else {
                $errorMessage = "Invalid file type. Please upload JPG, PNG, GIF, or WebP.";
            }
        }

        if (empty($errorMessage)) {
            $stmt = $conn->prepare("INSERT INTO tblClothes (item_name, description, size, color, category, price, seller_id, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param("sssssdss", $itemName, $description, $size, $color, $category, $price, $userId, $imagePath);

            if ($stmt->execute()) {
                $successMessage = 'Item listed successfully! Your "' . htmlspecialchars($itemName) . '" is now for sale.';
            } else {
                $errorMessage = "Error listing item: " . $conn->error;
            }
            $stmt->close();
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
    <title>Pastimes - Sell Item</title>
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
            max-width: 800px;
            margin: 0 auto;
        }
        .section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .section h2 {
            color: var(--dark);
            font-size: 24px;
            margin-bottom: 10px;
        }
        .section p {
            color: var(--gray);
            margin-bottom: 25px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .file-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        .file-upload:hover {
            border-color: var(--primary);
            background: #f0fdf4;
        }
        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .file-upload .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .file-upload p {
            margin: 0;
            color: var(--gray);
            font-size: 14px;
        }
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            margin-top: 15px;
            display: none;
        }
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover {
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
            <h2>➕ Sell Your Item</h2>
            <p>List your pre-loved fashion and give it a second life.</p>

            <?php if ($errorMessage): ?>
                <div class="message error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <?php if ($successMessage): ?>
                <div class="message success"><?php echo $successMessage; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" novalidate>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="item_image">Item Image *</label>
                        <div class="file-upload" id="fileUpload">
                            <input type="file" id="item_image" name="item_image" accept="image/*" required onchange="previewImage(this)">
                            <div class="icon">📷</div>
                            <p>Click to upload or drag and drop</p>
                            <p style="font-size: 12px; margin-top: 5px;">JPG, PNG, GIF up to 5MB</p>
                            <img id="preview" class="preview-image" alt="Preview">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="item_name">Item Name *</label>
                        <input type="text" id="item_name" name="item_name" required placeholder="e.g., Vintage Denim Jacket">
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required placeholder="Describe your item - condition, brand, material, etc."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (R) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="1" required placeholder="450.00">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="Top">Top</option>
                            <option value="Bottom">Bottom</option>
                            <option value="Dress">Dress</option>
                            <option value="Jacket">Jacket</option>
                            <option value="Shoes">Shoes</option>
                            <option value="Accessory">Accessory</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="size">Size</label>
                        <input type="text" id="size" name="size" placeholder="e.g., M, 32, 8">
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" placeholder="e.g., Blue, Black">
                    </div>
                </div>

                <button type="submit" name="sell_item" class="btn-submit">List Item for Sale</button>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const fileUpload = document.getElementById('fileUpload');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    fileUpload.querySelector('.icon').style.display = 'none';
                    fileUpload.querySelectorAll('p').forEach(p => p.style.display = 'none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>