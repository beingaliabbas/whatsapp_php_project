<?php
function get_setting($key, $default = null) {
    global $db;
    $stmt = $db->prepare("SELECT value FROM general_settings WHERE `key` = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

function send_whatsapp_message($phone, $message, $pdfPath = null) {
    global $db;

    $nodeServerUrl = rtrim(get_setting('node_server_url_admin'), '/');
    $baseUrl = rtrim(get_setting('base_url'), '/');
    $waApiKey = '123456';

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