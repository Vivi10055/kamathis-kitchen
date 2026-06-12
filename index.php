<?php
include "db.php";

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kamathi's Kitchen</title>

    <style>
        body {
            font-family: Arial;
            margin: 0;
            background: #f4f4f4;
        }

        .navbar {
            background: orange;
            padding: 15px;
            text-align: center;
            color: white;
            font-size: 22px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .card {
    background: white;
    width: 220px;
    margin: 15px;
    padding: 15px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-align: center;
    transition: 0.3s;
}
.card:hover {
    transform: scale(1.05);
}
            background: white;
            width: 200px;
            margin: 10px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        button {
    background: #ff9800;
    border: none;
    padding: 10px;
    color: white;
    width: 100%;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 10px;
    transition: 0.3s;
}

button:hover {
    background: #e68900;
    transform: scale(1.05);
}

        .price {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="navbar">
    🍔 Kamathi's Kitchen | Fast Food Delivery 🚀
</div>

<div class="container">

<?php
while($row = $result->fetch_assoc()) {
?>

<div class="card">
    <img src="images/<?php echo $row['image']; ?>" 
     style="width:100%; height:140px; object-fit:cover; border-radius:10px;">
    <h3><?php echo $row['name']; ?></h3>
    <p class="price">Ksh <?php echo $row['price']; ?></p>
    <p><?php echo $row['description']; ?></p>
    <a href="add_to_cart.php?id=<?php echo $row['id']; ?>">
    <button>Add to Cart</button>
</a>
</div>

<?php
}
?>

</div>

</body>
</html>