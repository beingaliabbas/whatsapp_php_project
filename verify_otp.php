<?php
require 'functions.php';
require 'whatsapp_send.php';
session_start();
header('Content-Type: application/json');

// 1) Verify OTP
$inputOtp = trim($_POST['otp'] ?? '');

if (!isset($_SESSION['otp_code'], $_SESSION['otp_time'])) {
    echo json_encode(['success' => false, 'error' => 'No OTP found.']);
    exit;
}
if (time() - $_SESSION['otp_time'] > 300) {
    echo json_encode(['success' => false, 'error' => 'OTP expired.']);
    exit;
}
if ($inputOtp !== (string)$_SESSION['otp_code']) {
    echo json_encode(['success' => false, 'error' => 'Invalid OTP.']);
    exit;
}

// 2) Grab pending data
if (empty($_SESSION['pending_user'])) {
    echo json_encode(['success' => false, 'error' => 'No registration data.']);
    exit;
}
$data = $_SESSION['pending_user'];

// 3) Register user
$userId = 'user_' . uniqid();
$reg = registerUserFull(
    $data['username'],
    $data['password'],
    $data['email'],
    $userId,
    $data['fullname'],
    $data['whatsapp']
);

if (!$reg) {
    echo json_encode(['success' => false, 'error' => 'Registration failed.']);
    exit;
}

// 4) Auto-login
$_SESSION['user_id']  = $userId;
$_SESSION['username'] = $data['username'];

// 5) Send greeting via centralized function
$greeting = "Hello {$data['fullname']}, welcome to WhatsApp API Service! 🎉";
$response = send_whatsapp_message($data['whatsapp'], $greeting);

if (!$response['success']) {
    // Optional: log error but don't block success
    // file_put_contents('wa_errors.log', $response['error']);
}

// 6) Clean up
unset($_SESSION['otp_code'], $_SESSION['otp_time'], $_SESSION['pending_user']);

// 7) Final response
echo json_encode(['success' => true, 'plan' => $_POST['plan'] ?? '']);
