<?php
require 'db.php'; // Include the database connection file

function loginUser($username, $password) {
    global $conn;
    // Sanitize input
    $username = filter_var($username, FILTER_SANITIZE_STRING);

    // Query to fetch user based on username or email
    $sql = "SELECT * FROM users WHERE username = :username OR email = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

/**
 * Extended registration: with full name and WhatsApp number.
 * WhatsApp number must be digits only (no +) and 10-15 digits.
 */
function registerUserFull($username, $password, $email, $userId, $fullname, $whatsapp) {
    global $conn;

    // Sanitize
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $fullname = filter_var($fullname, FILTER_SANITIZE_STRING);
    $whatsapp = preg_replace('/\D/', '', $whatsapp); // keep digits only
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database (users table must have fullname and whatsapp columns)
    $sql = "INSERT INTO users (username, password, email, user_id, fullname, whatsapp) VALUES (:username, :password, :email, :user_id, :fullname, :whatsapp)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
    $stmt->bindParam(':whatsapp', $whatsapp, PDO::PARAM_STR);

    if ($stmt->execute()) {
        return $userId;
    }
    return false;
}

/**
 * Legacy registration (username, email, password only).
 * You can remove if not needed.
 */
function registerUser($username, $password, $email) {
    global $conn;
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = password_hash($password, PASSWORD_DEFAULT);

    $userId = "user_" . uniqid();
    $sql = "INSERT INTO users (username, password, email, user_id) VALUES (:username, :password, :email, :user_id)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);

    if ($stmt->execute()) {
        return $userId;
    }
    return false;
}
function emailExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

function usernameExists($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetchColumn() > 0;
}
function getUserById($userId) {
    global $conn;
    $sql = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSetting($key) {
    global $conn;
    $sql = "SELECT value FROM general_settings WHERE `key` = :key LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':key', $key, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['value'] : null;
}

function isPlanActivated($user) {
    return isset($user['plan_activated']) && $user['plan_activated'] == 1;
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