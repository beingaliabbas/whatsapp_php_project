<?php
// register_ajax.php
require 'functions.php';
require 'whatsapp_send.php';
session_start();

header('Content-Type: application/json');

// 1) Collect & sanitize inputs
$data = [
    'username' => trim($_POST['username']  ?? ''),
    'password' => $_POST['password']       ?? '',
    'email'    => trim($_POST['email']     ?? ''),
    'fullname' => trim($_POST['fullname']  ?? ''),
    'whatsapp' => trim($_POST['whatsapp']  ?? ''),
    'plan'     => trim($_POST['plan']      ?? '')
];

// 2) Server-side validations
if (emailExists($data['email'])) {
    echo json_encode(['needOtp'=>false,'error'=>'This email address is already registered.']);
    exit;
}
if (usernameExists($data['username'])) {
    echo json_encode(['needOtp'=>false,'error'=>'This username is already taken.']);
    exit;
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['needOtp'=>false,'error'=>'Please provide a valid email address.']);
    exit;
}
if (strlen($data['username']) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
    echo json_encode(['needOtp'=>false,'error'=>'Username must be at least 3 characters and contain only letters, numbers, or underscores.']);
    exit;
}
if (strlen($data['password']) < 6) {
    echo json_encode(['needOtp'=>false,'error'=>'Password must be at least 6 characters long.']);
    exit;
}
if (strlen($data['fullname']) < 3 || !preg_match('/^[\p{L} \'-]+$/u', $data['fullname'])) {
    echo json_encode(['needOtp'=>false,'error'=>'Please enter your full name (letters, spaces, apostrophes, or hyphens only).']);
    exit;
}
if (!preg_match('/^\d{10,15}$/', $data['whatsapp'])) {
    echo json_encode(['needOtp'=>false,'error'=>'Please enter a valid WhatsApp number (digits only, 10–15 characters).']);
    exit;
}

// 3) Generate & store OTP + pending data
$otp = random_int(100000, 999999);
$_SESSION['otp_code']     = $otp;
$_SESSION['otp_time']     = time();
$_SESSION['pending_user'] = $data;

// 4) Send OTP via reusable function
$otpMsg = "Your verification code is: {$otp}";
$response = send_whatsapp_message($data['whatsapp'], $otpMsg);

if (!$response['success']) {
    echo json_encode(['needOtp'=>false, 'error' => "Failed to send OTP: {$response['error']}"]);
    exit;
}

// 5) OTP sent successfully
echo json_encode(['needOtp' => true]);
