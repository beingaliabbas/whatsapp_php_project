<?php
// whatsapp_send.php

/**
 * Fetch a setting from general_settings table by key.
 * Returns $default if not found.
 */
function get_setting($key, $default = null) {
    global $conn; // assumes you have a $conn PDO or mysqli connection
    $stmt = $conn->prepare("SELECT value FROM general_settings WHERE `key` = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

/**
 * Send WhatsApp message (text or PDF document) via the admin node server.
 *
 * @param string $phone      92300... format
 * @param string $message    Text message or caption for document
 * @param string|null $pdfPath Local path to PDF to send (optional)
 * @return array ['success' => bool, 'error' => string|null]
 */
function send_whatsapp_message($phone, $message, $pdfPath = null) {
    global $conn;

    // Always use admin node server
    $nodeServerUrl = rtrim(get_setting('node_server_url_admin'), '/');
    $baseUrl = rtrim(get_setting('base_url'), '/');

    $waApiKey = '123456'; // Or fetch from settings if needed

    if ($pdfPath && file_exists($pdfPath)) {
        $fileName = basename($pdfPath);
        $mediaUrl = $baseUrl . '/invoices/' . $fileName;
        $waUrl = $nodeServerUrl . '/send-media';
        $payload = [
            'apiKey'      => $waApiKey,
            'phoneNumber' => $phone,
            'caption'     => $message,
            'mediaUrl'    => $mediaUrl,
            'mediaType'   => 'document'
        ];
    } else {
        $waUrl = $nodeServerUrl . '/send-message';
        $payload = [
            'apiKey'      => $waApiKey,
            'phoneNumber' => $phone,
            'message'     => $message
        ];
    }

    $ch = curl_init($waUrl);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $waApiKey
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

    if ($waErr) {
        return ['success' => false, 'error' => "cURL error: $waErr"];
    }
    if (trim($waResp) === 'Message sent successfully') {
        return ['success' => true];
    }
    $waData = @json_decode($waResp, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (!empty($waData['success'])) return ['success' => true];
        if (!empty($waData['status'])) return ['success' => true];
    }
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "HTTP {$httpCode}: {$waResp}"];
    }
    if ($httpCode === 200) {
        return ['success' => true];
    }
    return ['success' => false, 'error' => $waResp ?: 'Unknown error'];
}