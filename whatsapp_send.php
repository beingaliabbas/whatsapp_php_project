<?php
// whatsapp_send.php

function send_whatsapp_message($phone, $message) {
    $waUrl = 'https://wa-baileys.beastsmm.pk/send-message';

    $payload = [
        'apiKey'      => '123456',
        'phoneNumber' => $phone,
        'message'     => $message
    ];

    $ch = curl_init($waUrl);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer 123456'
        ],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $waResp   = curl_exec($ch);
    $waErr    = curl_errno($ch) ? curl_error($ch) : null;
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 1) cURL error?
    if ($waErr) {
        return ['success' => false, 'error' => "cURL error: $waErr"];
    }

    // 2) Plain-text success?
    if (trim($waResp) === 'Message sent successfully') {
        return ['success' => true];
    }

    // 3) JSON decode
    $waData = @json_decode($waResp, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // 3a) { success: true } style
        if (!empty($waData['success'])) {
            return ['success' => true];
        }
        // 3b) { status: true } style
        if (!empty($waData['status'])) {
            return ['success' => true];
        }
    }

    // 4) HTTP code non-200?
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "HTTP {$httpCode}: {$waResp}"];
    }

    // 5) Fallback: treat any 200 as success
    if ($httpCode === 200) {
        return ['success' => true];
    }

    // 6) Otherwise error
    return ['success' => false, 'error' => $waResp ?: 'Unknown error'];
}
