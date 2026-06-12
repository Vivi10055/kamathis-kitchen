<?php
session_start();
include "db.php";

$id = $_GET['id'];

$sql = "SELECT * FROM products WHERE id = $id";
$result = $conn->query($sql);

$product = $result->fetch_assoc();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][] = [
    "name" => $product['name'],
    "price" => $product['price']
];

header("Location: cart.php");
exit();
?>