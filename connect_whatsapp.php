<?php
require 'functions.php'; // Ensure user authentication logic is here

$nodeServerURL = "http://localhost:3000"; // Node.js Server URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <script src="<?= $nodeServerURL ?>/socket.io/socket.io.js"></script>
</head>
<body class="bg-gray-900 text-white p-6 flex flex-col items-center">

    <!-- Status -->
    <div id="status" class="text-xl font-semibold">Connecting...</div>

    <!-- QR Code -->
    <div id="qr-container" class="mt-4 hidden">
        <p class="text-lg">Scan the QR code below:</p>
        <img id="qr-img" class="mt-4 max-w-full" alt="QR Code">
    </div>

    <!-- API Key Section -->
    <div class="mt-6">
        <p>Your API Key:</p>
        <input type="text" id="api-key" readonly class="p-2 rounded text-black">
        <button onclick="copyAPI()" class="ml-2 bg-yellow-500 p-2 rounded text-black">Copy</button>
    </div>

    <!-- Logout Button -->
    <button id="logout" class="mt-6 bg-red-600 p-2 rounded text-white">Logout</button>

    <script>
        const socket = io("<?= $nodeServerURL ?>", { transports: ["websocket", "polling"] });
        let apiKey = '';

        const elements = {
            status: document.getElementById('status'),
            qrContainer: document.getElementById('qr-container'),
            qrImg: document.getElementById('qr-img'),
            apiKey: document.getElementById('api-key'),
            logout: document.getElementById('logout')
        };

        // Handle connection issues
        socket.on('connect_error', () => updateStatus(false));

        // Handle status updates from Node.js
        socket.on('status', ({ ready, apiKey: key }) => updateStatus(ready, key));

        // Handle QR Code updates
        socket.on('qr', (qr) => showQR(qr));

        function updateStatus(ready, key = '') {
            elements.status.textContent = ready ? '✅ Connected' : '❌ Disconnected';
            elements.qrContainer.classList.toggle('hidden', ready);
            apiKey = key;
            elements.apiKey.value = key;
        }

        function showQR(qr) {
            elements.qrImg.src = qr;
            elements.qrContainer.classList.remove('hidden');
        }

        function copyAPI() {
            navigator.clipboard.writeText(elements.apiKey.value);
            alert('API Key copied!');
        }

        elements.logout.addEventListener('click', async () => {
            try {
                const response = await fetch("<?= $nodeServerURL ?>/logout", { method: 'POST' });
                if (!response.ok) throw new Error("Logout failed");
                const result = await response.json();
                alert(result.message);
                updateStatus(false);
            } catch (error) {
                console.error('Logout failed:', error);
                alert("Logout failed. Try again.");
            }
        });
    </script>

</body>
</html>
