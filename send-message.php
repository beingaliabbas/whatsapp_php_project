<?php
require 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "User ID not provided"]);
    exit;
}
$userId = $_GET['user_id'];

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON payload"]);
    exit;
}

$user = getUserById($userId);
if (!$user) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// Forward to Node.js server
$nodeServerURL = "http://localhost:3000/send-message/{$userId}";
$ch = curl_init($nodeServerURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $rawInput);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($rawInput)
]);
$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resultArr = @json_decode($response, true);

global $conn;
$phoneNumber = $data['phoneNumber'] ?? null;
$message = $data['message'] ?? null;

// Increment api_calls
$sql = "UPDATE users SET api_calls = api_calls + 1 WHERE user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
$stmt->execute();

$status = 'failure';
if ($resultArr && isset($resultArr['success']) && $resultArr['success']) {
    // Increment messages_sent
    $sql = "UPDATE users SET messages_sent = messages_sent + 1 WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $status = 'success';
}

// Log the attempt
$sql = "INSERT INTO user_message_logs (user_id, phone_number, sent_at, status, message_text) VALUES (:user_id, :phone_number, NOW(), :status, :message_text)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
$stmt->bindParam(':phone_number', $phoneNumber, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':message_text', $message, PDO::PARAM_STR);
$stmt->execute();

// Return Node.js response to client
http_response_code($httpStatus);
echo $response;
?>