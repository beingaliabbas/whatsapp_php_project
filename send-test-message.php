<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    die(json_encode(["success" => false, "message" => "User not logged in"]));
}

// Get user data
$userId = $_SESSION['user']['id']; // Assuming user ID is stored in the session
$apiKey = '6ef4c7ce8f1a6bd7412585b5351feed7'; // Provided API Key

// API endpoint
$apiUrl = "http://localhost:3000/send-message/$userId"; // Provided Endpoint

// Data to send in the request
$data = [
    'userId'      => $userId,  // Include user ID (use session or directly set it)
    'apiKey'      => $apiKey,  // Provided API key
    'phoneNumber' => '923483469617', // Replace with the recipient's phone number
    'message'     => 'Hello, this is Ali Abbas!' // Replace with the message
];

// Initialize cURL
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Execute cURL request and get the response
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    echo 'Response: ' . $response;
}

// Close cURL
curl_close($ch);
?>
