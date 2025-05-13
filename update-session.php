<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['apiKey'])) {
        $_SESSION['api_key'] = $data['apiKey'];
        http_response_code(200);
        exit();
    }
}
http_response_code(400);