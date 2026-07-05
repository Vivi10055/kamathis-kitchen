<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $conn->real_escape_string($_GET['id']);

$sql    = "SELECT * FROM products WHERE id = '$id'";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if ($product) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already in cart — increase qty
    $found = false;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] == $id) {
            $_SESSION['cart'][$index]['qty']++;
            $found = true;
            break;
        }
    }

    // If not found, add new item
    if (!$found) {
        $_SESSION['cart'][] = [
            'id'    => $product['id'],
            'name'  => $product['name'],
            'price' => $product['price'],
            'qty'   => 1
        ];
    }
}

header("Location: cart.php");
exit;
?>