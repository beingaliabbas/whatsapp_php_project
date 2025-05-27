<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit();
}
require 'functions.php';

// ==== GET USER ====
$userId = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);
$user = getUserById($userId);

// --- PLAN LOGIC: FIXED & RELIABLE ---
$now = new DateTime("now", new DateTimeZone("UTC"));
$planActivated    = ($user['plan_activated'] == 1);
$planEndDate      = $user['plan_end_date'] ?? null;
$planEndDateObj   = $planEndDate ? new DateTime($planEndDate, new DateTimeZone("UTC")) : null;
$planExpired      = !$planEndDateObj || ($now > $planEndDateObj);
$planNotActivated = !$planActivated || $planExpired;

// Calculate remaining days if applicable
$daysLeft = null;
if ($planActivated && $planEndDateObj && !$planExpired) {
    $interval = $now->diff($planEndDateObj);
    $daysLeft = $interval->days;
}

// Usage stats (from users table)
$messagesSent = (int)($user['messages_sent'] ?? 0);
$apiCalls = (int)($user['api_calls'] ?? 0);
$planQuota = (int)($user['plan_quota'] ?? 1000);
$quotaUsed = min($messagesSent, $planQuota);
$quotaPercent = $planQuota ? intval(($quotaUsed / max($planQuota,1)) * 100) : 0;

// Node server details
$nodeServerURL = getSetting('node_server_url') ?? 'http://localhost:3000';

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
$proxyEndpoint = $apiKey ? "http://localhost/whatsapp_php_project/send-message/$userId" : "";
$apiEndpoint = $proxyEndpoint;

// Handle message send (Quick Test)
$messageStatus = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['phone_number'])) {
    $phoneNumber = $_POST['phone_number'];
    $message = "Hello, this is a test message from your WhatsApp Api!";

    $apiUrl = "http://localhost/whatsapp_php_project/send-message/$userId";
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    $phpMsgStatus = "<span class='text-red-600 font-bold'>❌ Error sending message.</span>";

    $result = json_decode($response, true);
    if ($result && isset($result['success']) && $result['success']) {
        $phpMsgStatus = "<span class='text-green-600 font-bold'>✅ Message sent successfully!</span>";
    }

    $messageStatus = $phpMsgStatus;
    // Refresh user stats for display
    $user = getUserById($userId);
    $messagesSent = (int)($user['messages_sent'] ?? 0);
    $apiCalls = (int)($user['api_calls'] ?? 0);
    $quotaUsed = min($messagesSent, $planQuota);
    $quotaPercent = $planQuota ? intval(($quotaUsed / max($planQuota,1)) * 100) : 0;
}

// Get last 5 messages for stats panel
$recentMessages = [];
$sql = "SELECT * FROM user_message_logs WHERE user_id = :user_id ORDER BY sent_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
$stmt->execute();
$recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp API Dashboard</title>
    <script src="<?= $nodeServerURL ?>/socket.io/socket.io.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        html, body { margin:0; padding:0; box-sizing: border-box; background: #f4f6fb;}
        header { margin: 0; box-shadow: 0 2px 8px rgba(32,61,157,0.09);}
        .main-card { background:#fff; border-radius:1rem; box-shadow:0 2px 16px rgba(32,61,157,0.09); border:1px solid #e5e7eb;}
        .btn-primary { background-color: #203d9d; color: #fff; transition: background .2s;}
        .btn-primary:hover { background-color: #1a327d;}
        .btn-secondary { background-color: #4f46e5; color: #fff; transition: background .2s;}
        .btn-secondary:hover { background-color: #4338ca;}
        .copy-btn { opacity: 0.7; transition:.2s;}
        .copy-btn:hover { opacity:1; transform:scale(1.1);}
        .fade-in { animation: fadeIn 0.3s ease;}
        @keyframes fadeIn { from{ opacity:0;transform:translateY(10px);} to{ opacity:1;transform:translateY(0);}}
        .alert-box {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 1rem;
            background: #fffbeb;
            border-left: 4px solid #facc15;
            border-radius: .75rem;
            margin: 0 auto;
            max-width: 95%;
            margin-bottom: 1rem;
            box-shadow: 0 1px 8px rgba(250,202,21,0.08);
        }
        .alert-icon {
            margin-top: .25rem;
            color: #f59e42;
            font-size: 1.5rem;
        }
        .stat-bar-bg { background:#e0e7ff;}
        .stat-bar-fg { background:#6366f1;}
        .table-recent-messages th, .table-recent-messages td { padding: 0.5rem 0.7rem; font-size: 0.97em;}
        .table-recent-messages th { background: #f3f4f6; color: #374151;}
        .table-recent-messages tr:nth-child(even) td { background: #f9fafb;}
        .table-recent-messages tr td { background: #fff;}
        @media (min-width: 1024px) {
            .dashboard-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
            .dashboard-left { grid-column: span 1 / span 1; }
            .dashboard-main { grid-column: span 2 / span 2; }
        }
        @media (max-width: 1023px) {
            .dashboard-grid { display: block; }
            .dashboard-left, .dashboard-main { margin-bottom: 2rem; }
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Notification System -->
    <div id="notifications" class="fixed top-4 right-4 space-y-2 z-50 w-80"></div>

    <!-- Header -->
    <header class="w-full bg-[#203d9d] shadow-lg">
        <div class="flex items-center justify-between px-6 py-4 max-w-full">
            <a href="/" class="flex items-center gap-2 text-white hover:text-[#4f46e5] transition-colors">
                <i class="fab fa-whatsapp text-2xl"></i>
                <span class="text-xl font-bold">WhatsApp Suite</span>
            </a>
            <nav class="flex items-center gap-5">
                <a href="index" class="flex items-center gap-1 text-white hover:text-[#4f46e5] transition-colors">
                    <i class="fas fa-home"></i>
                    <span class="hidden sm:inline">Home</span>
                </a>
                <span class="text-white font-medium"><?= $username ?></span>
                <a href="logout" class="flex items-center gap-1 text-white hover:text-[#4f46e5] transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Welcome & Plan Status -->
    <section class="max-w-7xl mx-auto px-4 pt-8 pb-2">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#203d9d]">Welcome, <span class="text-[#4f46e5]"><?= $username ?></span>!</h1>
                <p class="text-gray-600 mt-1">
                    <?= $planNotActivated
                        ? 'Activate your subscription to unlock all features.'
                        : "Your plan is active. Enjoy the WhatsApp API Suite!"
                    ?>
                </p>
            </div>
            <?php if($planNotActivated): ?>
            <a href="index#plans" class="btn-primary px-6 py-2 rounded mt-2 md:mt-0 text-base flex items-center gap-2">
                <i class="fas fa-crown"></i>
                Upgrade / Activate Plan
            </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Dashboard Main Grid -->
    <main class="max-w-7xl mx-auto px-4 py-4 dashboard-grid">
        <!-- Left Sidebar: Plan & Connection & Support -->
        <aside class="dashboard-left space-y-6">
            <!-- Plan & Connection Section -->
            <div class="main-card p-6 fade-in">
                <h2 class="text-xl font-semibold text-[#203d9d] mb-4">Subscription & Connection</h2>
                <?php if ($planNotActivated): ?>
                    <div class="alert-box mb-0">
                        <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div>
                            <div class="font-bold text-yellow-800 mb-1"><?= $planExpired ? 'Plan Expired' : 'Plan Not Activated' ?></div>
                            <div class="text-yellow-900 text-sm mb-2">
                                <?= $planExpired 
                                    ? 'Your subscription has expired. Please renew your plan to use WhatsApp connection features.' 
                                    : 'Your subscription plan is not active. Please activate your plan to access connection features.' 
                                ?>
                            </div>
                            <a href="index#plans" class="btn-primary px-4 py-2 rounded inline-block">
                                View Plans
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if ($daysLeft !== null): ?>
                        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg my-4" role="alert">
                            <p class="font-bold">✅ Plan Active</p>
                            <p class="text-sm">You have <strong><?= $daysLeft ?></strong> day<?= $daysLeft != 1 ? 's' : '' ?> remaining in your subscription.</p>
                            <p class="text-xs text-gray-500">Plan ends on <strong><?= date('F j, Y', strtotime($planEndDate)) ?></strong>.</p>
                        </div>
                    <?php endif; ?>
                    <div id="qr-container" class="hidden">
                        <div class="mb-4">
                            <h3 class="text-gray-600 mb-2">Scan QR Code</h3>
                            <div class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <div class="loader absolute inset-0 m-auto ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
                                <img id="qr-img" class="w-full h-full object-contain hidden" src="" alt="QR Code" />
                            </div>
                        </div>
                        <button onclick="window.location.reload()" class="w-full py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                            Refresh Connection
                        </button>
                    </div>
                    <div id="connection-status" class="text-center py-4 hidden">
                        <div class="text-green-600 mb-2">✅ Connected Successfully</div>
                        <button id="logout-button" class="text-sm text-red-600 hover:text-red-500 transition-colors">
                            Disconnect Session
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- API Integration -->
            <div class="main-card p-6 fade-in">
                <h2 class="text-xl font-semibold text-[#203d9d] mb-4">API Integration</h2>
                <div class="space-y-6">
                    <div class="api-highlight p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600 font-medium">API Key:</span>
                            <?php if (!$planNotActivated): ?>
                            <button onclick="copyToClipboard('api-key')" class="copy-btn text-gray-400 hover:text-gray-600" aria-label="Copy API Key">
                                <i class="fas fa-copy"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <code id="api-key" class="text-[#4f46e5] break-all">
                            <?= (!$planNotActivated) ? htmlspecialchars($apiKey) : '<span class="text-gray-400">Not Available</span>' ?>
                        </code>
                    </div>
                    <div class="api-highlight p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600 font-medium">Endpoint:</span>
                            <?php if (!$planNotActivated): ?>
                            <button onclick="copyToClipboard('api-endpoint')" class="copy-btn text-gray-400 hover:text-gray-600" aria-label="Copy API Endpoint">
                                <i class="fas fa-copy"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <code id="api-endpoint" class="text-[#4f46e5] break-all">
                            <?= (!$planNotActivated) ? $apiEndpoint : '<span class="text-gray-400">Not Available</span>' ?>
                        </code>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="#" class="text-[#4f46e5] hover:text-[#4338ca] flex items-center justify-center">
                            <i class="fas fa-book mr-2"></i>
                            View API Documentation
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support Card -->
            <div class="main-card p-5 fade-in">
                <h2 class="text-lg font-semibold text-[#203d9d] mb-2">Need Help?</h2>
                <div class="flex items-center gap-2 text-gray-700 text-sm">
                    <i class="fas fa-life-ring text-[#4f46e5]"></i>
                    <span>Access our <a href="#" class="underline hover:text-[#203d9d]">support center</a> or <a href="#" class="underline hover:text-[#203d9d]">documentation</a>.</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area: Usage Stats & Quick Test -->
        <section class="dashboard-main space-y-6">
            <!-- Usage Stats -->
            <div class="main-card p-6 fade-in">
                <h2 class="text-xl font-semibold text-[#203d9d] mb-4">Usage Stats & Recent Messages</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="flex flex-col justify-between text-center">
                        <span class="text-xs text-gray-500 mb-1">Messages Sent</span>
                        <span class="text-2xl font-bold text-[#203d9d]"><?= $messagesSent ?></span>
                    </div>
                    <div class="flex flex-col justify-between text-center">
                        <span class="text-xs text-gray-500 mb-1">API Calls</span>
                        <span class="text-2xl font-bold text-[#203d9d]"><?= $apiCalls ?></span>
                    </div>
                    <div class="flex flex-col justify-between text-center">
                        <span class="text-xs text-gray-500 mb-1">Plan Quota</span>
                        <span class="text-2xl font-bold text-[#203d9d]"><?= $planQuota ?></span>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="text-sm text-gray-600">Quota Used</span>
                    <div class="w-full h-3 rounded-full stat-bar-bg mt-1 mb-1">
                        <div class="h-3 rounded-full stat-bar-fg transition-all" style="width:<?= $quotaPercent ?>%"></div>
                    </div>
                    <div class="text-xs text-gray-500"><?= $quotaUsed ?> / <?= $planQuota ?> (<?= $quotaPercent ?>%)</div>
                </div>
                
            </div>

            <!-- Quick Test Section -->
            <div class="main-card p-6 fade-in">
                <h2 class="text-xl font-semibold text-[#203d9d] mb-4">Quick Test</h2>
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <form method="POST" class="test-form-grid">
                            <div>
                                <label class="text-gray-600 block mb-2">Test Number</label>
                                <input type="tel" name="phone_number" 
                                    class="w-full p-3 bg-white text-gray-700 rounded-lg border border-gray-200 focus:border-[#4f46e5] focus:ring-2 focus:ring-[#4f46e5]/50 transition"
                                    placeholder="1234567890" 
                                    required />
                            </div>
                            <button type="submit" 
                                class="px-8 py-3 btn-secondary rounded-lg flex items-center justify-center text-base h-[56px] mt-6 sm:mt-0 sm:h-auto">
                                <i class="fas fa-paper-plane mr-2"></i>Send Test
                            </button>
                        </form>
                    </div>
                    <div id="message-status" class="p-4 rounded-lg bg-gray-50">
                        <?= $messageStatus ? 
                            '<div class="flex items-center gap-2 text-sm">' . 
                            (strpos($messageStatus, 'green') ? '' : '') .
                            '<span>' . $messageStatus . '</span></div>' : 
                            '<p class="text-gray-500 text-center">Test results will appear here</p>' ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

<script>
<?php if ($planNotActivated): ?>
    console.warn("Plan is inactive or expired. Socket not initialized.");
<?php else: ?>
    // Proceed only if the plan is active and not expired
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

    // Copy to clipboard function
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const text = element.innerText;

        navigator.clipboard.writeText(text).then(() => {
            showNotification('Copied to clipboard!', 'success');
        }).catch(err => {
            showNotification('Failed to copy', 'error');
        });
    }

    // Notification display
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
            elements.apiEndpoint.innerText = "http://localhost/whatsapp_php_project/send-message/<?= $userId ?>";
        } else {
            elements.apiKey.innerText = "Not Available";
            elements.apiEndpoint.innerText = "Not Available";
        }
    });

    socket.on('connect_error', () => {
        elements.status.innerHTML = '<span class="text-red-400">❌ Connection Error</span>';
        elements.qrContainer.classList.add('hidden');
    });

    elements.logoutButton && elements.logoutButton.addEventListener('click', async () => {
        await fetch("<?= $nodeServerURL ?>/logout/" + userId, { method: 'POST' });
        showNotification('Successfully logged out', 'success');
        socket.emit('check-status', userId);
    });
<?php endif; ?>
</script>
</body>
</html>