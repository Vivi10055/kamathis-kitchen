<?php
include 'db.php';

if(isset($_POST['register'])){

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $check = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($check);

    if($result->num_rows > 0){

        $error = "Email already exists!";

    } else {

        $sql = "INSERT INTO users(name, email, password)
                VALUES('$username', '$email', '$password')";

        if($conn->query($sql) === TRUE){

            header("Location: login.php");
            exit();

        } else {

            $error = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Kamathi's Kitchen</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, sans-serif;
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

        .register-box{
            width:400px;
            background:rgba(255,255,255,0.95);
            padding:30px;
            border-radius:15px;
            box-shadow:0 0 15px rgba(0,0,0,0.3);
        }

        h2{
            text-align:center;
            margin-bottom:20px;
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
            background:#ff6600;
            color:white;
            font-size:16px;
        }

        button:hover{
            background:#e65c00;
        }

        .error{
            color:red;
            text-align:center;
            margin-bottom:15px;
        }

        .login-link{
            text-align:center;
            margin-top:15px;
        }

        .login-link a{
            color:#ff6600;
            text-decoration:none;
            font-weight:bold;
        }

    </style>

</head>
<body>

<div class="register-box">

    <h2>Create Account</h2>

    <?php
    if(isset($error)){
        echo "<p class='error'>$error</p>";
    }
    ?>

    <form method="POST">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="register">
            Register
        </button>

    </form>

    <div class="login-link">
        <p>Already have an account?</p>
        <a href="login.php">Login Here</a>
    </div>

</div>

</body>
</html>