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
    $payment  = $conn->real_escape_string($_POST['payment_method']);

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $qty = $item['qty'] ?? 1;
        $total += $item['price'] * $qty;
    }

    $sql = "INSERT INTO orders (user_id, full_name, phone, address, notes, total, status, created_at)
            VALUES ('$user_id', '$name', '$phone', '$address', '$notes - Payment: $payment', '$total', 'pending', NOW())";

    if ($conn->query($sql)) {
        $order_id = $conn->insert_id;

        foreach ($_SESSION['cart'] as $item) {
            $qty   = $item['qty'] ?? 1;
            $pid   = $conn->real_escape_string($item['id']);
            $pname = $conn->real_escape_string($item['name']);
            $price = $conn->real_escape_string($item['price']);
            $conn->query("INSERT INTO order_items (order_id, product_id, product_name, price)
                          VALUES ('$order_id', '$pid', '$pname', '$price')");
        }

        $_SESSION['cart'] = [];
        $success = ['id' => $order_id, 'payment' => $payment, 'total' => $total];
    } else {
        $error = "Order failed. Please try again.";
    }
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $qty = $item['qty'] ?? 1;
    $total += $item['price'] * $qty;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kamathi's Kitchen</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: Arial, sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding: 70px 20px 40px;
        }
        .overlay {
            position: fixed;
            top:0; left:0; right:0; bottom:0;
            background: rgba(0,0,0,0.6);
            z-index: 0;
        }
        nav {
            background: rgba(0,0,0,0.85);
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top:0; left:0; right:0;
            z-index: 1000;
        }
        nav .logo { color:#e65c00; font-size:1.3rem; font-weight:bold; text-decoration:none; }
        nav .nav-links a { color:#fff; text-decoration:none; margin-left:20px; font-size:0.95rem; }
        nav .nav-links a:hover { color:#e65c00; }

        .container {
            position: relative;
            z-index: 1;
            max-width: 960px;
            margin: 30px auto;
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
            margin-bottom: 28px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .card h2 {
            font-size: 1.1rem;
            margin-bottom: 18px;
            color: #333;
            border-bottom: 2px solid #e65c00;
            padding-bottom: 10px;
        }
        .field { margin-bottom: 14px; }
        .field label {
            display: block;
            font-size: 0.83rem;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .field input, .field textarea, .field select {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 0.92rem;
            font-family: Arial, sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .field input:focus, .field textarea:focus, .field select:focus {
            border-color: #e65c00;
        }
        .field textarea { resize: none; height: 70px; }

        /* Payment methods */
        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 16px;
        }
        .payment-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .payment-option:hover { border-color: #e65c00; background: #fff8f2; }
        .payment-option input[type="radio"] { accent-color: #e65c00; width: 18px; height: 18px; }
        .payment-option.selected { border-color: #e65c00; background: #fff3e0; }
        .payment-icon { font-size: 1.6rem; }
        .payment-label { font-weight: bold; font-size: 0.95rem; color: #333; }
        .payment-desc { font-size: 0.78rem; color: #888; }

        /* Payment detail panels */
        .payment-detail { display: none; margin-top: 12px; }
        .payment-detail.active { display: block; }
        .mpesa-info {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.88rem;
            color: #2e7d32;
        }
        .card-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.88rem;
            color: #1565c0;
            margin-bottom: 12px;
        }
        .cash-info {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.88rem;
            color: #f57f17;
        }

        /* Order summary */
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.92rem;
            color: #444;
        }
        .order-item:last-of-type { border-bottom: none; }
        .order-item span:last-child { font-weight: bold; color: #e65c00; }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0 0;
            border-top: 2px solid #e65c00;
            margin-top: 8px;
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
            margin-top: 18px;
            transition: background 0.2s;
        }
        .btn-checkout:hover { background: #c94f00; }

        .btn-back {
            display: inline-block;
            margin-bottom: 18px;
            color: #fff;
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 20px;
        }
        .btn-back:hover { background: rgba(255,255,255,0.25); }

        .error-msg {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        /* Success screen */
        .success-box {
            background: #fff;
            border-radius: 16px;
            padding: 50px 40px;
            text-align: center;
            max-width: 500px;
            margin: 60px auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .success-icon { font-size: 4rem; margin-bottom: 14px; }
        .success-box h2 { font-size: 1.6rem; color: #2e7d32; margin-bottom: 8px; }
        .success-box p { color: #666; margin-bottom: 6px; font-size: 0.95rem; }
        .order-number {
            font-size: 1.1rem; font-weight: bold;
            color: #e65c00; background: #fff3e0;
            padding: 10px 20px; border-radius: 8px;
            display: inline-block; margin: 10px 0;
        }
        .payment-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            margin: 8px 0 16px;
        }
        .badge-mpesa  { background: #e8f5e9; color: #2e7d32; }
        .badge-card   { background: #e3f2fd; color: #1565c0; }
        .badge-cash   { background: #fff8e1; color: #f57f17; }
        .btn-menu {
            display: inline-block;
            margin-top: 16px;
            padding: 12px 28px;
            background: #e65c00;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .btn-menu:hover { background: #c94f00; }

        @media (max-width: 650px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="overlay"></div>

<nav>
    <a href="menu.php" class="logo">🍽️ Kamathi's Kitchen</a>
    <div class="nav-links">
        <a href="menu.php">Menu</a>
        <a href="cart.php">🛒 Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

<?php if ($success): ?>
<!-- ── Success Screen ── -->
<div class="success-box">
    <div class="success-icon">🎉</div>
    <h2>Order Placed!</h2>
    <p>Thank you for your order, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    <div class="order-number">Order #<?= $success['id'] ?></div><br>

    <?php if ($success['payment'] === 'mpesa'): ?>
        <div class="payment-badge badge-mpesa">📱 M-Pesa Payment</div>
        <p>An STK push has been sent to your phone.<br>Enter your M-Pesa PIN to complete payment.</p>
        <p style="color:#2e7d32; font-weight:bold; margin-top:8px;">Amount: Ksh <?= number_format($success['total']) ?></p>
    <?php elseif ($success['payment'] === 'card'): ?>
        <div class="payment-badge badge-card">💳 Card Payment</div>
        <p>Your card has been charged successfully.</p>
        <p style="color:#1565c0; font-weight:bold; margin-top:8px;">Amount: Ksh <?= number_format($success['total']) ?></p>
    <?php else: ?>
        <div class="payment-badge badge-cash">💵 Cash on Delivery</div>
        <p>Please have <strong>Ksh <?= number_format($success['total']) ?></strong><br>ready when your order arrives.</p>
    <?php endif; ?>

    <br>
    <p style="color:#aaa; font-size:.85rem;">We'll prepare your food right away! 🍽️</p>
    <a href="menu.php" class="btn-menu">Back to Menu</a>
</div>

<?php else: ?>
<!-- ── Checkout Form ── -->
<a href="cart.php" class="btn-back">← Back to Cart</a>
<h1>🧾 Checkout</h1>
<p class="subtitle">Fill in your details and choose how to pay</p>

<?php if ($error): ?>
    <div class="error-msg">⚠️ <?= $error ?></div>
<?php endif; ?>

<form method="POST" action="" id="checkoutForm">
    <div class="grid">

        <!-- Left: Details + Payment -->
        <div>
            <div class="card" style="margin-bottom:20px;">
                <h2>📋 Your Details</h2>
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="full_name"
                        value="<?= htmlspecialchars($_SESSION['username']) ?>" required>
                </div>
                <div class="field">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="e.g. 0712 345 678" required>
                </div>
                <div class="field">
                    <label>Delivery Address</label>
                    <input type="text" name="address" placeholder="Street, building, area" required>
                </div>
                <div class="field">
                    <label>Special Instructions (optional)</label>
                    <textarea name="notes" placeholder="e.g. Extra sauce, no onions..."></textarea>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card">
                <h2>💳 Payment Method</h2>
                <div class="payment-options">

                    <!-- M-Pesa -->
                    <label class="payment-option" onclick="selectPayment('mpesa')">
                        <input type="radio" name="payment_method" value="mpesa" required>
                        <div class="payment-icon">📱</div>
                        <div>
                            <div class="payment-label">M-Pesa</div>
                            <div class="payment-desc">Pay via M-Pesa STK Push</div>
                        </div>
                    </label>

                    <!-- Card -->
                    <label class="payment-option" onclick="selectPayment('card')">
                        <input type="radio" name="payment_method" value="card">
                        <div class="payment-icon">💳</div>
                        <div>
                            <div class="payment-label">Card Payment</div>
                            <div class="payment-desc">Visa / Mastercard / Credit Card</div>
                        </div>
                    </label>

                    <!-- Cash -->
                    <label class="payment-option" onclick="selectPayment('cash')">
                        <input type="radio" name="payment_method" value="cash">
                        <div class="payment-icon">💵</div>
                        <div>
                            <div class="payment-label">Cash on Delivery</div>
                            <div class="payment-desc">Pay when your order arrives</div>
                        </div>
                    </label>

                </div>

                <!-- M-Pesa details -->
                <div class="payment-detail" id="detail-mpesa">
                    <div class="field">
                        <label>M-Pesa Phone Number</label>
                        <input type="tel" name="mpesa_phone" placeholder="e.g. 0712 345 678">
                    </div>
                    <div class="mpesa-info">
                        📲 An STK push will be sent to your phone. Enter your M-Pesa PIN to confirm payment.
                    </div>
                </div>

                <!-- Card details -->
                <div class="payment-detail" id="detail-card">
                    <div class="card-info">💳 Card payments are processed securely.</div>
                    <div class="field">
                        <label>Card Number</label>
                        <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="field">
                            <label>Expiry Date</label>
                            <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="field">
                            <label>CVV</label>
                            <input type="text" name="card_cvv" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>

                <!-- Cash details -->
                <div class="payment-detail" id="detail-cash">
                    <div class="cash-info">
                        💵 Please have exact change ready. Our rider will collect payment on delivery.
                    </div>
                </div>

            </div>
        </div>

        <!-- Right: Order Summary -->
        <div class="card">
            <h2>🛒 Order Summary</h2>
            <?php foreach ($_SESSION['cart'] as $item):
                $qty = $item['qty'] ?? 1;
                $subtotal = $item['price'] * $qty;
            ?>
            <div class="order-item">
                <span><?= htmlspecialchars($item['name']) ?> x<?= $qty ?></span>
                <span>Ksh <?= number_format($subtotal) ?></span>
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

<script>
function selectPayment(method) {
    // Remove selected class from all
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    // Hide all detail panels
    document.querySelectorAll('.payment-detail').forEach(el => el.classList.remove('active'));
    // Add selected to clicked
    event.currentTarget.classList.add('selected');
    // Show relevant panel
    document.getElementById('detail-' + method).classList.add('active');
}

// Format card number with spaces
document.addEventListener('input', function(e) {
    if (e.target.name === 'card_number') {
        let val = e.target.value.replace(/\D/g, '').substring(0, 16);
        e.target.value = val.replace(/(.{4})/g, '$1 ').trim();
    }
    if (e.target.name === 'card_expiry') {
        let val = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (val.length >= 2) val = val.substring(0,2) + '/' + val.substring(2);
        e.target.value = val;
    }
});
</script>
</body>
</html>