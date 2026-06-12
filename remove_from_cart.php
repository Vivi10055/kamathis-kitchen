<?php
session_start();

$id = $_GET['id'];

if(isset($_SESSION['cart'][$id])){
    unset($_SESSION['cart'][$id]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

header("Location: cart.php");
exit();
?>