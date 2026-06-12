<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: cart.php");
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $user_id  = $_SESSION['user_id'];
    $name     = $conn->real_escape_string($_POST['full_name']);
    $phone    = $conn->real_escape_string($_POST['phone']);
    $address  = $conn->real_escape_string($_POST['address']);
    $notes    = $conn->real_escape_string($_POST['notes'] ?? '');

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'];
    }

    $sql = "INSERT INTO orders (user_id, full_name, phone, address, notes, total, status, created_at)
            VALUES ('$user_id', '$name', '$phone', '$address', '$notes', '$total', 'pending', NOW())";

    if ($conn->query($sql)) {
        $order_id = $conn->insert_id;

        foreach ($_SESSION['cart'] as $item) {
            $pid   = $conn->real_escape_string($item['id']);
            $pname = $conn->real_escape_string($item['name']);
            $price = $conn->real_escape_string($item['price']);
            $conn->query("INSERT INTO order_items (order_id, product_id, product_name, price)
                          VALUES ('$order_id', '$pid', '$pname', '$price')");
        }

        $_SESSION['cart'] = [];
        $success = $order_id;

    } else {
        $error = "Order failed. Please try again.";
    }
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kamathi's Kitchen</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding: 30px 20px;
        }
        .overlay {
            background: rgba(0,0,0,0.55);
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #fff;
            font-size: 2rem;
            margin-bottom: 6px;
            text-shadow: 2px 2px 4px #000;
        }
        .subtitle {
            text-align: center;
            color: #f0c080;
            margin-bottom: 30px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .card h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #e65c00;
            padding-bottom: 10px;
        }
        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            font-size: 0.85rem;
            font-weight: bold;
            color: #555;
            margin-bottom: 6px;
        }
        .field input, .field textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: Arial, sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .field input:focus, .field textarea:focus {
            border-color: #e65c00;
        }
        .field textarea { resize: none; height: 80px; }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.95rem;
            color: #444;
        }
        .order-item span:last-child { font-weight: bold; color: #e65c00; }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 14px 0 0;
            border-top: 2px solid #e65c00;
            margin-top: 10px;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .total-row span:last-child { color: #e65c00; }
        .btn-checkout {
            width: 100%;
            padding: 14px;
            background: #e65c00;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.2s;
        }
        .btn-checkout:hover { background: #c94f00; }
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #fff;
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 20px;
        }
        .btn-back:hover { background: rgba(255,255,255,0.25); }
        .success-box {
            background: #fff;
            border-radius: 12px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-width: 500px;
            margin: 60px auto;
        }
        .success-icon { font-size: 4rem; margin-bottom: 16px; }
        .success-box h2 { font-size: 1.6rem; color: #2e7d32; margin-bottom: 10px; }
        .success-box p { color: #666; margin-bottom: 8px; }
        .order-number {
            font-size: 1.1rem;
            font-weight: bold;
            color: #e65c00;
            background: #fff3e0;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            margin: 12px 0;
        }
        .btn-menu {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 28px;
            background: #e65c00;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .btn-menu:hover { background: #c94f00; }
        .error-msg {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        @media (max-width: 650px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="overlay"></div>
<div class="container">

    <?php if ($success): ?>
    <div class="success-box">
        <div class="success-icon">🎉</div>
        <h2>Order Placed!</h2>
        <p>Thank you for your order.</p>
        <div class="order-number">Order #<?= $success ?></div>
        <p style="color:#888; font-size:.9rem;">We'll prepare your food right away!</p>
        <a href="menu.php" class="btn-menu">Back to Menu</a>
    </div>

    <?php else: ?>
    <a href="cart.php" class="btn-back">← Back to Cart</a>
    <h1>🧾 Checkout</h1>
    <p class="subtitle">Review your order and fill in your details</p>

    <?php if ($error): ?>
        <div class="error-msg">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="grid">
            <div class="card">
                <h2>📋 Your Details</h2>
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="full_name"
                        value="<?= htmlspecialchars($_SESSION['username']) ?>"
                        required>
                </div>
                <div class="field">
                    <label>Phone Number</label>
                    <input type="tel" name="phone"
                        placeholder="e.g. 0712 345 678" required>
                </div>
                <div class="field">
                    <label>Delivery Address</label>
                    <input type="text" name="address"
                        placeholder="Street, building, area" required>
                </div>
                <div class="field">
                    <label>Special Instructions (optional)</label>
                    <textarea name="notes" placeholder="e.g. Extra sauce, no onions..."></textarea>
                </div>
            </div>

            <div class="card">
                <h2>🛒 Order Summary</h2>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="order-item">
                        <span><?= htmlspecialchars($item['name']) ?></span>
                        <span>Ksh <?= number_format($item['price']) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="total-row">
                    <span>Total</span>
                    <span>Ksh <?= number_format($total) ?></span>
                </div>
                <button type="submit" name="place_order" class="btn-checkout">
                    ✅ Place Order
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>

</div>
</body>
</html>