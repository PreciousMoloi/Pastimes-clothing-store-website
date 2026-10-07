<?php
/**
 * loadClothingStore.php
 * Master script that drops and recreates ALL tables in ClothingStore database
 * Uses MySQLi for database operations
 * Includes DBConn.php for connection
 * NO auto-populated data - all data entered via browser forms
 */

// Include the database connection file
require_once 'DBConn.php';

// Establish database connection
$conn = getDBConnection();

echo "<h1 style='color: #2dd4bf; font-family: Arial;'>Pastimes - ClothingStore Database Setup</h1>";
echo "<hr>";

// ============================================
// FUNCTION: Drop table if exists
// ============================================
/**
 * @param mysqli $conn Database connection
 * @param string $tableName Name of table to drop
 * @return bool
 */
function dropTable($conn, $tableName) {
    $sql = "DROP TABLE IF EXISTS $tableName";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Dropped table: $tableName</p>";
        return true;
    } else {
        echo "<p style='color: red;'>✗ Error dropping $tableName: " . $conn->error . "</p>";
        return false;
    }
}

// ============================================
// FUNCTION: Create table
// ============================================
/**
 * @param mysqli $conn Database connection
 * @param string $tableName Name of table to create
 * @param string $createSQL SQL statement to create table
 * @return bool
 */
function createTable($conn, $tableName, $createSQL) {
    if ($conn->query($createSQL) === TRUE) {
        echo "<p style='color: green;'>✓ Created table: $tableName</p>";
        return true;
    } else {
        echo "<p style='color: red;'>✗ Error creating $tableName: " . $conn->error . "</p>";
        return false;
    }
}

// ============================================
// STEP 1: Drop all existing tables (in reverse order due to foreign keys)
// ============================================
echo "<h2>Step 1: Dropping Existing Tables</h2>";
dropTable($conn, 'tblAorder');
dropTable($conn, 'tblClothes');
dropTable($conn, 'tblAdmin');
dropTable($conn, 'tblUser');

// ============================================
// STEP 2: Create tblUser
// ============================================
echo "<h2>Step 2: Creating tblUser</h2>";
$createUserSQL = "CREATE TABLE tblUser (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20),
    is_verified TINYINT(1) DEFAULT 0,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
createTable($conn, 'tblUser', $createUserSQL);

echo "<p style='color: #64748b;'>→ Users must register via browser and be verified by admin before login.</p>";

// ============================================
// STEP 3: Create tblAdmin
// ============================================
echo "<h2>Step 3: Creating tblAdmin</h2>";
$createAdminSQL = "CREATE TABLE tblAdmin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
createTable($conn, 'tblAdmin', $createAdminSQL);

echo "<p style='color: #64748b;'>→ Admins must register manually via admin_login.php. Email must contain '@pastimes'.</p>";

// ============================================
// STEP 4: Create tblClothes (with status for admin moderation)
// ============================================
echo "<h2>Step 4: Creating tblClothes</h2>";
$createClothesSQL = "CREATE TABLE tblClothes (
    clothes_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    size VARCHAR(20),
    color VARCHAR(50),
    category VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    seller_id INT,
    image_path VARCHAR(255),
    status ENUM('active','sold','pending') DEFAULT 'active',
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES tblUser(user_id)
)";
createTable($conn, 'tblClothes', $createClothesSQL);

echo "<p style='color: #64748b;'>→ Items added by sellers via sell_item.php with image upload.</p>";

// ============================================
// STEP 5: Create tblAorder (Orders table with admin status management)
// ============================================
echo "<h2>Step 5: Creating tblAorder</h2>";
$createOrderSQL = "CREATE TABLE tblAorder (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    clothes_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    FOREIGN KEY (buyer_id) REFERENCES tblUser(user_id),
    FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id)
)";
createTable($conn, 'tblAorder', $createOrderSQL);

echo "<p style='color: #64748b;'>→ Orders created during checkout. Admin updates status via dashboard.</p>";

// ============================================
// SUMMARY
// ============================================
echo "<hr><h2>Setup Complete!</h2>";
echo "<p>All tables created successfully. The database is ready for use.</p>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Go to <a href='admin_login.php' style='color: #2dd4bf;'>Admin Login</a> and register your first admin (email must contain @pastimes)</li>";
echo "<li>Register customers via <a href='login.php' style='color: #2dd4bf;'>Customer Login</a></li>";
echo "<li>Verify customers in the Admin Dashboard</li>";
echo "<li>Start buying and selling!</li>";
echo "</ol>";

// Close connection
closeDBConnection($conn);

?>
<p><a href='index.php' style='color: #2dd4bf; font-size: 18px;'>← Back to Home</a></p>