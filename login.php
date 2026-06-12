<?php
session_start();
include 'db.php';

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){

        $user = $result->fetch_assoc();

        if($password == $user['password']){

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];

            header("Location: menu.php");
            exit();

        } else {
            $error = "Incorrect password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Kamathi's Kitchen</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family: Arial, sans-serif;
        }

        body{
            background-image:url('images/background.png');
            background-size:cover;
            background-position:center;
            background-repeat:no-repeat;

            display:flex;
            justify-content:center;
            align-items:center;

            height:100vh;
        }

        .login-box{
            width:350px;
            background:rgba(255,255,255,0.95);
            padding:30px;
            border-radius:15px;
            box-shadow:0 0 15px rgba(0,0,0,0.3);
        }

        h2{
            text-align:center;
            margin-bottom:20px;
            color:#333;
        }

        label{
            font-weight:bold;
        }

        input{
            width:100%;
            padding:10px;
            margin-top:5px;
            margin-bottom:15px;
            border:1px solid #ccc;
            border-radius:5px;
        }

        button{
            width:100%;
            padding:12px;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
            background:#ff6600;
            color:white;
        }

        button:hover{
            background:#e65c00;
        }

        .error{
            color:red;
            text-align:center;
            margin-bottom:15px;
        }

        .register-link{
            text-align:center;
            margin-top:15px;
        }

        .register-link a{
            text-decoration:none;
            color:#ff6600;
            font-weight:bold;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h2>Kamathi's Kitchen</h2>

    <?php
    if(isset($error)){
        echo "<p class='error'>$error</p>";
    }
    ?>

    <form method="POST">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">Login</button>

    </form>

    <div class="register-link">
        <p>Don't have an account?</p>
        <a href="register.php">Register Here</a>
    </div>

</div>

</body>
</html>