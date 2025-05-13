<?php
// send-message.php

// Ensure the request method is POST (or you can adjust as needed)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// Get user_id from the URL query parameter (populated by the rewrite rule)
if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "User ID not provided"]);
    exit;
}
$userId = $_GET['user_id'];

// Get the request payload
$rawInput = file_get_contents('php://input');

// (Optional) You can also add validation/sanitization of $userId and $rawInput here

// Define the Node.js endpoint URL using the provided user_id
$nodeServerURL = "http://localhost:3000/send-message/{$userId}";

// Initialize a cURL session to forward the request
$ch = curl_init($nodeServerURL);

// Set cURL options to forward the POST data and headers
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $rawInput);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($rawInput)
]);

// Execute the request to the Node.js server
$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Set the response code from Node.js (if desired)
http_response_code($httpStatus);

// Return the Node.js response to the client
header('Content-Type: application/json');
echo $response;
?>
