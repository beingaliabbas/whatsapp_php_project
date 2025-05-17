<?php
require 'db.php'; // Include the database connection file

function loginUser($username, $password) {
    global $conn; // Use the global $conn from db.php

    // Sanitize input
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    // Query to fetch user based on username
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if password matches (assuming passwords are hashed in the database)
        if (password_verify($password, $user['password'])) {
            return $user; // Return user data including user_id
        }
    }

    return false; // Return false if login fails
}

function registerUser($username, $password, $email) {
    global $conn;

    // Sanitize inputs
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $password = password_hash($password, PASSWORD_DEFAULT); // Hash password
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Generate a unique user ID (Example: user_65c1a89f4b8e5)
    $userId = "user_" . uniqid();

    // Insert user into the database
    $sql = "INSERT INTO users (username, password, email, user_id) VALUES (:username, :password, :email, :user_id)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);

    if ($stmt->execute()) {
        return $userId; // Return user ID upon successful registration
    }

    return false; // Registration failed
}
function getUserById($userId) {
    global $conn; // ✅ Use global connection like other functions

    $sql = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
