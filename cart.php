<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle quantity update
if (isset($_POST['update_qty'])) {
    $index = $_POST['index'];
    $qty   = (int)$_POST['qty'];
    if ($qty <= 0) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    } else {
        $_SESSION['cart'][$index]['qty'] = $qty;
    }
}

// Handle remove
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Handle clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// Calculate total
$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $qty = $item['qty'] ?? 1;
        $total += $item['price'] * $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Kamathi's Kitchen</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: Arial, sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
        }
        .overlay {
            position: fixed;
            top:0; left:0; right:0; bottom:0;
            background: rgba(0,0,0,0.55);
            z-index: 0;
        }

        /* Navbar */
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
        nav .logo {
            color: #e65c00;
            font-size: 1.3rem;
            font-weight: bold;
            text-decoration: none;
        }
        nav .nav-links a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.95rem;
        }
        nav .nav-links a:hover { color: #e65c00; }

        /* Page content */
        .container {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h1 {
            text-align: center;
            color: #fff;
            font-size: 2rem;
            margin-bottom: 24px;
            text-shadow: 2px 2px 4px #000;
        }

        /* Empty cart */
        .empty-cart {
            background: #fff;
            border-radius: 12px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .empty-cart .icon { font-size: 4rem; margin-bottom: 16px; }
        .empty-cart h2 { color: #333; margin-bottom: 10px; }
        .empty-cart p { color: #888; margin-bottom: 24px; }
        .btn-menu {
            display: inline-block;
            padding: 12px 28px;
            background: #e65c00;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .btn-menu:hover { background: #c94f00; }

        /* Cart card */
        .cart-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            margin-bottom: 16px;
        }

        /* Cart item row */
        .cart-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            gap: 16px;
        }
        .cart-item:last-child { border-bottom: none; }

        .item-emoji { font-size: 2rem; flex-shrink: 0; }

        .item-info { flex: 1; }
        .item-name { font-weight: bold; font-size: 1rem; color: #222; }
        .item-price { color: #e65c00; font-size: 0.9rem; margin-top: 3px; }

        /* Quantity controls */
        .qty-form { display: flex; align-items: center; gap: 8px; }
        .qty-btn {
            width: 32px; height: 32px;
            background: #f0f0f0;
            border: none; border-radius: 50%;
            font-size: 1.1rem; font-weight: bold;
            cursor: pointer; color: #333;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .qty-btn:hover { background: #e65c00; color: #fff; }
        .qty-input {
            width: 40px; height: 32px;
            text-align: center;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: bold;
        }

        .item-subtotal {
            font-weight: bold;
            color: #222;
            font-size: 1rem;
            min-width: 80px;
            text-align: right;
        }

        .btn-remove {
            background: #fff0f0;
            border: 1px solid #ffcdd2;
            color: #e53935;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-remove:hover { background: #ffcdd2; }

        /* Summary section */
        .summary {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.95rem;
            color: #555;
            border-bottom: 1px solid #f0f0f0;
        }
        .summary-row:last-of-type { border-bottom: none; }
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 14px 0 0;
            font-size: 1.2rem;
            font-weight: bold;
            color: #222;
            border-top: 2px solid #e65c00;
            margin-top: 8px;
        }
        .summary-total span:last-child { color: #e65c00; }

        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        .btn-checkout {
            flex: 1;
            padding: 14px;
            background: #e65c00;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: background 0.2s;
        }
        .btn-checkout:hover { background: #c94f00; }
        .btn-clear {
            padding: 14px 20px;
            background: #fff;
            color: #e53935;
            border: 1.5px solid #e53935;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: block;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-clear:hover { background: #ffebee; }
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
    <h1>🛒 Your Cart</h1>

    <?php if (empty($_SESSION['cart'])): ?>
    <div class="empty-cart">
        <div class="icon">🛒</div>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added anything yet.</p>
        <a href="menu.php" class="btn-menu">Browse Menu</a>
    </div>

    <?php else: ?>
    <!-- Cart Items -->
    <div class="cart-card">
        <?php
        $emojis = ['ugali' => '🍛', 'chicken' => '🍗', 'fries' => '🍟', 'soda' => '🥤'];
        foreach ($_SESSION['cart'] as $index => $item):
            $qty      = $item['qty'] ?? 1;
            $subtotal = $item['price'] * $qty;
            $name_lower = strtolower($item['name']);
            $emoji = '🍽️';
            foreach ($emojis as $key => $e) {
                if (strpos($name_lower, $key) !== false) { $emoji = $e; break; }
            }
        ?>
        <div class="cart-item">
            <div class="item-emoji"><?= $emoji ?></div>
            <div class="item-info">
                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="item-price">Ksh <?= number_format($item['price']) ?> each</div>
            </div>

            <!-- Quantity buttons -->
            <form method="POST" action="" class="qty-form">
                <input type="hidden" name="index" value="<?= $index ?>">
                <button type="submit" name="update_qty" class="qty-btn"
                    onclick="this.form.qty.value=Math.max(0,parseInt(this.form.qty.value)-1)">−</button>
                <input type="number" name="qty" value="<?= $qty ?>"
                    min="0" max="20" class="qty-input">
                <button type="submit" name="update_qty" class="qty-btn"
                    onclick="this.form.qty.value=Math.min(20,parseInt(this.form.qty.value)+1)">+</button>
            </form>

            <div class="item-subtotal">Ksh <?= number_format($subtotal) ?></div>

            <a href="cart.php?remove=<?= $index ?>" class="btn-remove">🗑️ Remove</a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Order Summary -->
    <div class="summary">
        <div class="summary-row">
            <span>Items (<?= count($_SESSION['cart']) ?>)</span>
            <span>Ksh <?= number_format($total) ?></span>
        </div>
        <div class="summary-row">
            <span>Delivery</span>
            <span style="color:#2e7d32;">Free</span>
        </div>
        <div class="summary-total">
            <span>Total</span>
            <span>Ksh <?= number_format($total) ?></span>
        </div>

        <div class="btn-row">
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout →</a>
            <a href="cart.php?clear=1" class="btn-clear"
               onclick="return confirm('Clear all items?')">🗑️ Clear</a>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>