<?php

$conn = new mysqli("localhost", "root", "", "kamathis_kitchen");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>