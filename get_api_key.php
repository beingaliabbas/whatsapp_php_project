<?php
session_start();
header('Content-Type: application/json');
require 'functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'] ?? null;
$password = $data['password'] ?? '';
$requestedUserId = $data['user_id'] ?? '';

if (!$userId || $requestedUserId != $userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = getUserById($userId);
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Make sure your users table stores a password hash using password_hash()
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    exit;
}

// Fetch API key from Node server
$nodeServerURL = getSetting('node_server_url') ?? 'http://localhost:3000';
$apiKeyResponse = @file_get_contents("$nodeServerURL/get-api-key/$userId");
$apiKey = null;
if ($apiKeyResponse) {
    $apiKeyData = json_decode($apiKeyResponse, true);
    if (isset($apiKeyData['apiKey'])) {
        $apiKey = $apiKeyData['apiKey'];
    }
}
if ($apiKey) {
    echo json_encode(['success' => true, 'apiKey' => $apiKey]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch API key.']);
}