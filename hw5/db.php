<?php
$connString = "mysql:host=localhost;port=8889;dbname=shopdb";
$user = "root";
$pwd = "root";
//Connects to the shop database. 
try {
    $pdo = new PDO($connString, $user, $pwd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
