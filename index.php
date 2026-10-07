<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Pre-loved Fashion, Newly Adored</title>
    <style>
        :root {
            --primary: #2dd4bf;
            --primary-dark: #14b8a6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }
        .logo {
            font-size: 48px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }
        .logo span { color: var(--primary); }
        .tagline {
            font-size: 20px;
            color: var(--gray);
            margin-bottom: 40px;
        }
        .hero {
            background: white;
            border-radius: 24px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }
        .hero h2 {
            font-size: 36px;
            color: var(--dark);
            margin-bottom: 15px;
        }
        .hero p {
            font-size: 18px;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(45, 212, 191, 0.3);
        }
        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .feature-card .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        .feature-card h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        .feature-card p {
            color: var(--gray);
            font-size: 14px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
            color: var(--gray);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Pastimes<span>.</span></div>
        <p class="tagline">Pre-loved fashion, newly adored</p>

        <div class="hero">
            <h2>Welcome to Pastimes</h2>
            <p>Your sustainable fashion marketplace. Buy and sell pre-loved branded clothing with confidence. Join our community of eco-conscious fashion lovers.</p>

            <div class="btn-group">
                <a href="login.php" class="btn btn-primary">Customer Login</a>
                <a href="admin_login.php" class="btn btn-secondary">Admin Portal</a>
            </div>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="icon">🛍️</div>
                <h3>Browse Items</h3>
                <p>Explore our curated collection of secondhand branded clothing at amazing prices.</p>
            </div>
            <div class="feature-card">
                <div class="icon">✅</div>
                <h3>Verified Sellers</h3>
                <p>All sellers are verified by our admin team for a safe shopping experience.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🌱</div>
                <h3>Sustainable</h3>
                <p>Give clothes a second life and reduce fashion waste. Shop responsibly.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🛒</div>
                <h3>Easy Checkout</h3>
                <p>Simple cart system with secure checkout process. Buy with confidence.</p>
            </div>
        </div>

        <div class="footer">
            <p>© 2026 Pastimes. All rights reserved. | WEDE6021 POE Part 2</p>
        </div>
    </div>
</body>
</html>