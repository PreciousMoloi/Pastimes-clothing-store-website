PASTIMES - PART 2 SETUP INSTRUCTIONS
=====================================

1. EXTRACT FILES
   - Extract all files to your WAMP www folder (e.g., C:\wamp64\www\pastimes)
   - Ensure the 'images' folder is writable (right-click > Properties > Security)

2. DATABASE SETUP
   - Open browser and go to: http://localhost/pastimes/loadClothingStore.php
   - This will create the ClothingStore database with all tables:
     * tblUser (customers)
     * tblAdmin (administrators - MUST register via browser)
     * tblClothes (items for sale)
     * tblAorder (orders)

3. REGISTER FIRST ADMIN (CRITICAL - No pre-populated admins!)
   - Go to: http://localhost/pastimes/admin_login.php
   - Click "Register here" (shown when no admins exist)
   - Enter email that contains @pastimes (e.g., admin@pastimes.co.za)
   - Enter password (min 8 characters)
   - Select role (Super Admin, Store Manager, etc.)
   - Click "Register Admin"
   - Admin details are saved to tblAdmin in database

4. ADMIN LOGIN REQUIREMENTS
   - Email MUST contain '@pastimes' (e.g., staff@pastimes.co.za)
   - This ensures only staff members can access admin panel
   - Password is hashed with md5 and stored in database

5. FILE STRUCTURE
   pastimes/
   ├── index.php              (Homepage)
   ├── login.php              (Customer login & register)
   ├── admin_login.php        (Admin login & register)
   ├── user_dashboard.php     (Browse items, add to cart)
   ├── cart.php               (View cart, remove items)
   ├── checkout.php           (Place orders)
   ├── sell_item.php          (Sell items with image upload)
   ├── admin_dashboard.php    (Manage users & orders)
   ├── logout.php             (Logout)
   ├── DBConn.php             (Database connection)
   ├── loadClothingStore.php  (Create tables)
   └── images/                (Uploaded clothing images)

6. FEATURES IMPLEMENTED
   ✓ User registration (pending admin verification)
   ✓ Login with password hash comparison
   ✓ Sticky form on error
   ✓ "User [Name] is logged in" message
   ✓ Associative array read from database
   ✓ Add to cart with popup showing SellPrice
   ✓ Shopping cart with item images
   ✓ Checkout process (shipping + payment)
   ✓ Sell item with image upload (stored in images/ folder)
   ✓ Images display in browser from uploaded folder
   ✓ Admin register via browser (email must contain @pastimes)
   ✓ Admin verify new registrations
   ✓ Admin add/update/delete users
   ✓ Admin update order status (Pending/Processing/Shipped/Completed/Cancelled)
   ✓ Order management with item images

7. IMPORTANT NOTES
   - ALL data is entered via browser forms (NO .txt files used in Part 2)
   - Admin must register manually - no pre-populated admin accounts
   - Admin email MUST contain '@pastimes' to identify as staff
   - Images are uploaded to the images/ folder and URL saved in database
   - Admin must verify users before they can login
   - Order status can be updated by admin in the Order Management tab
