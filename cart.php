<?php
session_start();

echo "<h1>Shopping Cart</h1>";

$total = 0;

if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0){

    echo "<ul>";

    foreach($_SESSION['cart'] as $index => $item){

    echo "<li>
            " . $item['name'] . " - Ksh " . $item['price'] . "
            <a href='remove_from_cart.php?id=$index'>Remove</a>
          </li>";

    $total += $item['price'];
}

    echo "</ul>";

    echo "<h3>Total: Ksh " . $total . "</h3>";
    echo "<a href='checkout.php' style='display:block; text-align:center; margin-top:16px; padding:12px; background:#e65c00; color:#fff; text-decoration:none; border-radius:8px; font-weight:bold;'>Proceed to Checkout →</a>";

} else {
    echo "Your cart is empty.";
}
?>