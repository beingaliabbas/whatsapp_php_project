<?php
$url = "http://localhost/whatsapp_php_project/api/v1/users/user_6838c395c809a/send";

// Correct field names: apiKey, phoneNumber, message
$data = [
    "apiKey" => "896dfd7561597923776b4cc688583d32",
    "phoneNumber" => "923483469617",
    "message" => "Hello, this is a test message from PHP!"
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
} else {
    echo "Response: " . $response;
}

curl_close($ch);
?>

<?php
$url = "http://localhost/whatsapp_php_project/api/v1/users/user_67a5180bd273e/send";

// Correct field names: apiKey, phoneNumber, message
$data = [
    "apiKey" => "c9bb8ba21335cd29a822f54575b152f6",
    "phoneNumber" => "923483469617",
    "message" => "Hello, this is a test message from PHP!"
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
} else {
    echo "Response: " . $response;
}

curl_close($ch);
?>

