<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'functions.php';

$nodeServerURL = "http://localhost:3000";
$userId = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Fetch API Key from Node.js Server (with error handling)
$apiKey = $_SESSION['api_key'] ?? null;
if (!$apiKey) {
    $apiKeyResponse = @file_get_contents("$nodeServerURL/get-api-key/$userId");
    if ($apiKeyResponse) {
        $apiKeyData = json_decode($apiKeyResponse, true);
        if (isset($apiKeyData['apiKey'])) {
            $_SESSION['api_key'] = $apiKeyData['apiKey'];
            $apiKey = $_SESSION['api_key'];
        }
    }
}

// Use your own domain for the send-message endpoint.
$proxyEndpoint = $apiKey ? "https://localhost/whatsapp_php_project/send-message/$userId" : "";
$apiEndpoint = $proxyEndpoint;

$messageStatus = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['phone_number'])) {
    $phoneNumber = $_POST['phone_number'];
    $message = "Hello, this is a test message from your WhatsApp Api!";

    // Use the proxy endpoint on your PHP domain
    $apiUrl = "https://localhost/whatsapp_php_project/send-message/$userId";
    $data = json_encode([
        'userId'    => $userId,
        'apiKey'    => $apiKey,
        'phoneNumber' => $phoneNumber,
        'message'   => $message
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    // Disable SSL verification on localhost (remove or change for production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($result && isset($result['success']) && $result['success']) {
        $messageStatus = "<span class='text-green-500'>✅ Message sent successfully!</span>";
    } else {
        // Optionally log $response or $curlError for debugging
        $messageStatus = "<span class='text-red-500'>❌ Error sending message.</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp API Dashboard</title>
    <!-- Socket.io and Tailwind CSS -->
    <script src="<?= $nodeServerURL ?>/socket.io/socket.io.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        .loader {
            border-top-color: #3B82F6;
            animation: spinner 1.5s linear infinite;
        }
        @keyframes spinner {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .section-card {
            background: linear-gradient(145deg, #1f2937, #111827);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .dark .section-card {
            background: linear-gradient(145deg, #2d3748, #1a202c);
        }
        .api-highlight {
            background: rgba(59, 130, 246, 0.15);
            border-left: 4px solid #3B82F6;
        }
        .copy-btn {
            transition: all 0.2s;
            opacity: 0.7;
        }
        .copy-btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen dark:bg-gray-800">
    <!-- Notification System -->
    <div id="notifications" class="fixed top-4 right-4 space-y-2 z-50 w-80">
        <div id="success-alert" class="hidden p-3 bg-green-800/90 text-green-100 rounded-lg border border-green-600">
            <i class="fas fa-check-circle mr-2"></i>
            <span>Message sent successfully!</span>
        </div>
        <div id="error-alert" class="hidden p-3 bg-red-800/90 text-red-100 rounded-lg border border-red-600">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>Failed to send message</span>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-indigo-400 dark:text-indigo-300">WhatsApp Automation Suite</h1>
                <p class="text-gray-400 mt-1 dark:text-gray-300">Welcome, <?= $username ?>!</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="logout.php" class="text-red-500 hover:text-red-300 transition-colors">Logout</a>
                <div id="header-status" class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-gray-600 pulse"></div>
                    <span id="status" class="text-gray-300 dark:text-gray-200">Connecting...</span>
                </div>
            </div>
        </header>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Connection Section -->
                <div class="section-card rounded-xl p-6 shadow-xl fade-in">
                    <h2 class="text-xl font-semibold text-white dark:text-gray-100 mb-4">Connection Manager</h2>
                    <div id="qr-container" class="hidden">
                        <div class="mb-4">
                            <h3 class="text-gray-300 mb-2 dark:text-gray-200">Scan QR Code</h3>
                            <div class="relative aspect-square bg-gray-800 rounded-lg overflow-hidden">
                                <div class="loader absolute inset-0 m-auto ease-linear rounded-full border-4 border-t-4 border-gray-700 h-12 w-12"></div>
                                <img id="qr-img" class="w-full h-full object-contain hidden" src="" alt="QR Code" />
                            </div>
                        </div>
                        <button onclick="window.location.reload()" class="w-full py-2 px-4 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                            Refresh Connection
                        </button>
                    </div>
                    <div id="connection-status" class="text-center py-4 hidden">
                        <div class="text-green-400 mb-2">✅ Connected Successfully</div>
                        <button id="logout-button" class="text-sm text-red-400 hover:text-red-300 transition-colors">
                            Disconnect Session
                        </button>
                    </div>
                </div>

                <!-- Enhanced API Section -->
                <div class="section-card rounded-xl p-6 shadow-xl fade-in">
                    <h2 class="text-xl font-semibold text-white dark:text-gray-100 mb-4">API Integration</h2>
                    <div class="space-y-6">
                        <!-- API Key Block -->
                        <div class="api-highlight p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-300 font-medium">API Key:</span>
                                <button onclick="copyToClipboard('api-key')" 
                                        class="copy-btn text-gray-400 hover:text-white">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <code id="api-key" class="text-green-400 break-all"><?= htmlspecialchars($apiKey ?? "Not Available") ?></code>
                        </div>

                        <!-- Endpoint Block -->
                        <div class="api-highlight p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-300 font-medium">Endpoint:</span>
                                <button onclick="copyToClipboard('api-endpoint')" 
                                        class="copy-btn text-gray-400 hover:text-white">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <code id="api-endpoint" class="text-blue-400 break-all"><?= $apiEndpoint ?: "Not Available" ?></code>
                        </div>

                        <!-- Documentation Link -->
                        <div class="mt-4 text-center">
                            <a href="#" class="text-indigo-400 hover:text-indigo-300 flex items-center justify-center">
                                <i class="fas fa-book mr-2"></i>
                                View API Documentation
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Test Section -->
                <div class="section-card rounded-xl p-6 shadow-xl fade-in">
                    <h2 class="text-xl font-semibold text-white dark:text-gray-100 mb-4">Quick Test</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-700 p-4 rounded-lg">
                            <form method="POST" class="flex gap-4 items-end">
                                <div class="flex-1">
                                    <label class="text-gray-300 block mb-2 dark:text-gray-200">Test Number</label>
                                    <input type="tel" name="phone_number" 
                                           class="w-full p-3 bg-gray-800 text-white rounded-lg border border-gray-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-900" 
                                           placeholder="1234567890" 
                                           required />
                                </div>
                                <button type="submit" 
                                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors flex items-center justify-center">
                                    <i class="fas fa-paper-plane mr-2"></i>Send Test
                                </button>
                            </form>
                        </div>

                        <!-- Test Result -->
                        <div id="message-status" class="p-4 rounded-lg bg-gray-800">
                            <?= $messageStatus ? 
                                '<div class="flex items-center gap-2 text-sm">' . 
                                (strpos($messageStatus, 'green') ? '' : '') .
                                '<span>' . $messageStatus . '</span></div>' : 
                                '<p class="text-gray-400 text-center">Test results will appear here</p>' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update the socket connection to use the Node.js server as usual.
        const socket = io("<?= $nodeServerURL ?>");
        const userId = "<?= $userId ?>";
        const elements = {
            status: document.getElementById('status'),
            qrContainer: document.getElementById('qr-container'),
            qrImg: document.getElementById('qr-img'),
            logoutButton: document.getElementById('logout-button'),
            connectionStatus: document.getElementById('connection-status'),
            apiKey: document.getElementById('api-key'),
            apiEndpoint: document.getElementById('api-endpoint')
        };

        // Copy functionality
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.innerText;
            
            navigator.clipboard.writeText(text).then(() => {
                showNotification('Copied to clipboard!', 'success');
            }).catch(err => {
                showNotification('Failed to copy', 'error');
            });
        }

        // Enhanced notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `p-3 ${type === 'success' ? 'bg-green-800/90' : 'bg-blue-800/90'} text-white rounded-lg border ${type === 'success' ? 'border-green-600' : 'border-blue-600'} fade-in`;
            notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'} mr-2"></i>${message}`;
            
            document.getElementById('notifications').appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        socket.emit('register-user', userId);

        socket.on(`qr-${userId}`, (qr) => {
            elements.qrImg.src = qr;
            elements.qrContainer.classList.remove('hidden');
            elements.qrImg.classList.remove('hidden');
        });

        socket.on(`status-${userId}`, ({ ready, apiKey }) => {
            elements.status.innerHTML = ready 
                ? '<span class="text-green-400">✅ Connected to WhatsApp</span>'
                : '<span class="text-red-400">❌ Not Connected</span>';
            elements.qrContainer.classList.toggle('hidden', ready);
            elements.connectionStatus.classList.toggle('hidden', !ready);
            
            if (ready && apiKey) {
                elements.apiKey.innerText = apiKey;
                // Use your PHP domain proxy endpoint for the API endpoint display
                elements.apiEndpoint.innerText = "https://localhost/whatsapp_php_project/send-message/<?= $userId ?>";
            } else {
                elements.apiKey.innerText = "Not Available";
                elements.apiEndpoint.innerText = "Not Available";
            }
        });

        socket.on('connect_error', () => {
            elements.status.innerHTML = '<span class="text-red-400">❌ Connection Error</span>';
            elements.qrContainer.classList.add('hidden');
        });

        elements.logoutButton.addEventListener('click', async () => {
            await fetch("<?= $nodeServerURL ?>/logout/" + userId, { method: 'POST' });
            showNotification('Successfully logged out', 'success');
            socket.emit('check-status', userId);
        });
    </script>
</body>
</html>
