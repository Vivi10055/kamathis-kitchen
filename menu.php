<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kamathi's Kitchen Menu</title>

    <style>

        body{
    font-family: Arial, sans-serif;
    background: url('images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    margin:0;
    padding:20px;
padding-top: 70px;
}
nav {
    background: rgba(0,0,0,0.75);
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

        h1{
            text-align:center;
        }

        .container{
            display:flex;
            flex-wrap:wrap;
            justify-content:center;
            gap:20px;
        }

        .card{
            background:white;
            width:250px;
            padding:15px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
            text-align:center;
        }

        .card img{
            width:100%;
            height:180px;
            object-fit:cover;
            border-radius:10px;
        }

        .btn{
            display:inline-block;
            margin-top:10px;
            padding:10px 15px;
            background:#ff6600;
            color:white;
            text-decoration:none;
            border-radius:5px;
        }

        .btn:hover{
            background:#e65c00;
        }

    </style>
</head>

<body>
    <nav>
    <a href="menu.php" class="logo">🍽️ Kamathi's Kitchen</a>
    <div class="nav-links">
        <a href="menu.php">Menu</a>
        <a href="cart.php">🛒 Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<h1 style="text-align:center; color:#fff; text-shadow: 2px 2px 4px #000;">Welcome <?php echo $_SESSION['username']; ?></h1>
<h2 style="text-align:center; color:#fff; text-shadow: 2px 2px 4px #000;">Kamathi's Kitchen Menu</h2>

<div class="container">

<?php

$result = $conn->query("SELECT * FROM products");

while($row = $result->fetch_assoc()){

?>

<div class="card">

    <img src="images/<?php echo $row['image']; ?>">

    <h3><?php echo $row['name']; ?></h3>

    <p>Ksh <?php echo $row['price']; ?></p>

    <a class="btn"
       href="add_to_cart.php?id=<?php echo $row['id']; ?>">
       Add to Cart
    </a>

</div>

<?php
}
?>

</div>

</body>
</html>