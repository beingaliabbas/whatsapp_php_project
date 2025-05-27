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
function getSetting($key) {
    global $conn; // Use PDO connection from db.php

    $sql = "SELECT value FROM general_settings WHERE `key` = :key LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':key', $key, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['value'] : null;
}
function isPlanActivated($user) {
    return (bool) ($user['plan_activated'] ?? 0);
}

function isPlanExpired($user) {
    $endDate = $user['plan_end_date'] ?? null;
    return $endDate && (strtotime($endDate) < time());
}

function getDaysLeft($user) {
    if (!isPlanActivated($user) || isPlanExpired($user)) return null;

    $now = new DateTime();
    $end = new DateTime($user['plan_end_date']);
    return $now->diff($end)->days;
}

?>
