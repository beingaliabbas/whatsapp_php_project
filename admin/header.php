<?php
if (!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - WhatsApp API</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
<nav class="bg-indigo-700 text-white px-4 py-2 flex justify-between items-center shadow">
    <div class="flex items-center space-x-4">
        <a href="index.php" class="text-xl font-bold">WA API Admin</a>
        <a href="users.php" class="hover:underline">Users</a>
        <a href="orders.php" class="hover:underline">Orders</a>
        <a href="message_logs.php" class="hover:underline">Logs</a>
        <a href="payment_settings.php" class="hover:underline">Payment</a>
        <a href="settings.php" class="hover:underline">Settings</a>
    </div>
    <div class="flex items-center space-x-3">
        <span class="text-sm hidden md:inline">Admin: <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></strong></span>
        <a href="logout.php" class="bg-red-600 px-3 py-1 rounded hover:bg-red-700 text-white text-sm font-semibold ml-2">Logout</a>
    </div>
</nav>
<div class="container mx-auto py-6 px-2">