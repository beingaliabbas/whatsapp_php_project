<?php
// Database connection using PDO
$host = "localhost";
$dbname = "whatsapp_test";
$username = "root";
$password = "";

try {
    // Establishing the connection to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error mode
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
