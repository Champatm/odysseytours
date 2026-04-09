<?php 
$host = "localhost";
$dbname = "secure_login";
$username = "root";
$password = "";  //this are database credentials tell Php : Where to find the database (localhost), the name of the database to use(dbname), who is connecting and password to access the database.

try { // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password); // Creates connction to MySQL server using pdo

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exceptions for errors tells pdo if something goes wrng ,throw error instead failing silently
    // Create Database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS secure_login";
    $pdo->exec($sql); 

    

} catch (PDOException $e) { //if anything fails that is :connection ,wrong credentials,mysqldown
    die("Connection failed: " . $e->getMessage());
}
?>

