<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once "functions.php"; // getSetting()

// Fetch general settings
$base_url = rtrim(getSetting('base_url'), '/') . '/';
$logo_path = $base_url . "assets/logo.png";
$favicon = getSetting('favicon') ?: ($base_url . "assets/favicon.ico");
$keywords = getSetting('site_keywords') ?: "WhatsApp API, messaging, automation, business, Pakistan";
$meta_description = isset($meta_description) ? $meta_description : (getSetting('site_description') ?: "Easily send WhatsApp messages using our business API. Quick, reliable, and secure!");
$page_title = isset($page_title) ? $page_title : (getSetting('site_title') ?: "WhatsApp API Dashboard");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
    <link rel="icon" href="<?= htmlspecialchars($favicon) ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= htmlspecialchars($favicon) ?>" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($favicon) ?>">
    <!-- Tailwind or other CSS links -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Any other global head includes -->
</head>
<body>