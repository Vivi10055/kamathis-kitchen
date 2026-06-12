<?php
session_start();
include 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = $conn->real_escape_string($_POST['order_id']);
    $status   = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE id='$order_id'");
}

// Fetch all orders
$orders = $conn->query("SELECT orders.*, users.name FROM orders 
                        JOIN users ON orders.user_id = users.id 
                        ORDER BY orders.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Kamathi's Kitchen</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        nav {
            background: #1a1a1a;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0; left: 0; right: 0;
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
        .container {
            max-width: 1100px;
            margin: 80px auto 0;
        }
        h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 24px;
        }
        /* Stats row */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #e65c00;
        }
        .stat-card .label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 4px;
        }
        /* Orders table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        th {
            background: #1a1a1a;
            color: #fff;
            padding: 14px 16px;
            text-align: left;
            font-size: 0.9rem;
        }
        td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
            color: #444;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fff8f4; }
        /* Status badge */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pending    { background: #fff3cd; color: #856404; }
        .badge-confirmed  { background: #cce5ff; color: #004085; }
        .badge-preparing  { background: #fff0e0; color: #e65c00; }
        .badge-ready      { background: #d4edda; color: #155724; }
        .badge-delivered  { background: #d1ecf1; color: #0c5460; }
        .badge-cancelled  { background: #f8d7da; color: #721c24; }
        /* Status form */
        select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .btn-update {
            padding: 6px 14px;
            background: #e65c00;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-left: 6px;
        }
        .btn-update:hover { background: #c94f00; }
        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>

<nav>
    <a href="admin.php" class="logo">🍽️ Kamathi's Kitchen — Admin</a>
    <div class="nav-links">
        <a href="menu.php">View Menu</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>📦 Orders Dashboard</h1>

    <?php
    // Stats
    $total_orders   = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
    $total_revenue  = $conn->query("SELECT SUM(total) as t FROM orders WHERE status != 'cancelled'")->fetch_assoc()['t'];
    $pending_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
    ?>

    <div class="stats">
        <div class="stat-card">
            <div class="number"><?= $total_orders ?></div>
            <div class="label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $pending_orders ?></div>
            <div class="label">Pending Orders</div>
        </div>
        <div class="stat-card">
            <div class="number">Ksh <?= number_format($total_revenue) ?></div>
            <div class="label">Total Revenue</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
            <tr>
                <td>#<?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['name']) ?></td>
                <td><?= htmlspecialchars($order['phone']) ?></td>
                <td><?= htmlspecialchars($order['address']) ?></td>
                <td>Ksh <?= number_format($order['total']) ?></td>
                <td>
                    <span class="badge badge-<?= $order['status'] ?>">
                        <?= $order['status'] ?>
                    </span>
                </td>
                <td><?= date('d M, H:i', strtotime($order['created_at'])) ?></td>
                <td>
                    <form method="POST" style="display:flex; align-items:center;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status">
                            <option value="pending"   <?= $order['status']=='pending'   ? 'selected':'' ?>>Pending</option>
                            <option value="confirmed" <?= $order['status']=='confirmed' ? 'selected':'' ?>>Confirmed</option>
                            <option value="preparing" <?= $order['status']=='preparing' ? 'selected':'' ?>>Preparing</option>
                            <option value="ready"     <?= $order['status']=='ready'     ? 'selected':'' ?>>Ready</option>
                            <option value="delivered" <?= $order['status']=='delivered' ? 'selected':'' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status']=='cancelled' ? 'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-update">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" class="empty">No orders yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>