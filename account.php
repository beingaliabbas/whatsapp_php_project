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
if (!$user) {
    session_destroy();
    header('Location: login?error=usernotfound');
    exit();
}

// --- BASE URL FROM SETTINGS ---
$baseUrl = rtrim(getSetting('base_url'), '/') . '/';

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
$apiKey = null;
$apiKeyResponse = @file_get_contents("$nodeServerURL/get-api-key/$userId");
if ($apiKeyResponse) {
    $apiKeyData = json_decode($apiKeyResponse, true);
    if (isset($apiKeyData['apiKey'])) {
        $apiKey = $apiKeyData['apiKey'];
        $_SESSION['api_key'] = $apiKey;
    }
}

// --- NEW: Fetch WhatsApp linked number & name ---
$waNumber = $waName = null;
$waInfoResponse = @file_get_contents("$nodeServerURL/user-info/$userId");
if ($waInfoResponse) {
    $waInfo = json_decode($waInfoResponse, true);
    if (isset($waInfo['success']) && $waInfo['success']) {
        $waNumber = $waInfo['number'];
        $waName = $waInfo['name'];
    }
}

// Use your own domain for the send-message endpoint.
$proxyEndpoint = $apiKey ? "{$baseUrl}api/v1/users/$userId/send" : "";
$apiEndpoint = $proxyEndpoint;

// Handle message send (Quick Test)
$messageStatus = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['phone_number'])) {
    $phoneNumber = $_POST['phone_number'];
    $message = "Hello, how are you? You have successfully received the test message :)";

    $apiUrl = "{$baseUrl}api/v1/users/$userId/send";
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
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Account Dashboard</title>
  <script>
    window.DASHBOARD_VARS = {
      userId: "<?= $userId ?>",
      apiKey: "<?= $apiKey ?>",
      nodeServerURL: "<?= $nodeServerURL ?>",
      planNotActivated: <?= $planNotActivated ? 'true' : 'false' ?>
    };
  </script>
  <script src="<?= $nodeServerURL ?>/socket.io/socket.io.js"></script>
  <script src="assets/dashboard.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg,#e0e7ff 0%,#f4f6fb 100%);
    }
    .glass {
      background: rgba(255,255,255,0.88);
      box-shadow: 0 2px 12px 0 rgba(32,61,157,0.10);
      backdrop-filter: blur(7px);
      border-radius: 1rem;
      border: 1px solid rgba(76,81,255,0.06);
    }
    .fade-in { animation: fadeIn 0.5s cubic-bezier(.4,0,.2,1);}
    @keyframes fadeIn { from{ opacity:0;transform:translateY(12px);} to{ opacity:1;transform:translateY(0);}}
    .floating-btn {
      position: fixed;
      bottom: 34px;
      right: 34px;
      z-index: 50;
      background: linear-gradient(135deg,#4f46e5 60%,#203d9d 100%);
      color: #fff;
      border-radius: 50%;
      width: 54px; height: 54px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 18px 0 rgba(32,61,157,0.16);
      transition: background .17s;
    }
    .floating-btn:hover { background: #203d9d; }
    .notification-glass {
      background: rgba(76,81,255,0.81);
      backdrop-filter: blur(5px);
      color: #fff;
      border-radius: 0.7rem;
      padding: 1rem 1.3rem;
      box-shadow: 0 3px 14px 0 rgba(76,81,255,0.13);
      font-weight: 600;
    }
    ::selection { background: #4f46e5; color:#fff; }
    .stat-main { font-size: 2rem; }
    .stat-label { letter-spacing: 0.01em; }
    .glass code { font-size: 0.98em; }
    .btn-cta {
      background: linear-gradient(90deg,#203d9d 60%,#4f46e5 100%);
      color: #fff;
      border-radius: 0.7rem;
      font-weight: 600;
      transition: background .17s;
    }
    .btn-cta:hover { background: #4f46e5; }
  </style>
</head>
<body class="min-h-screen">
  <!-- Floating Notification System -->
  <div id="notifications" class="fixed top-6 right-6 space-y-2 z-50 w-96"></div>

  <!-- Header -->
  <header class="w-full bg-gradient-to-r from-[#203d9d] to-[#4f46e5] shadow-lg fade-in">
    <div class="flex items-center justify-between px-8 py-5 max-w-full">
      <a href="<?= $baseUrl ?>" class="flex items-center gap-3 text-white hover:text-yellow-300 transition-colors">
        <i class="fab fa-whatsapp text-2xl drop-shadow"></i>
        <span class="text-2xl font-bold tracking-tight drop-shadow">WhatsApp Suite</span>
      </a>
      <nav class="flex items-center gap-6 text-white">
        <a href="<?= $baseUrl ?>index" class="hover:text-yellow-300 transition"><i class="fas fa-home"></i> <span class="hidden sm:inline ml-1">Home</span></a>
        <span class="font-semibold"><?= $username ?></span>
        <a href="<?= $baseUrl ?>logout" class="hover:text-yellow-300 transition"><i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline ml-1">Logout</span></a>
      </nav>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-3 md:px-8 py-10 grid grid-cols-1 lg:grid-cols-3 gap-8 fade-in">
    <!-- Sidebar -->
    <aside class="lg:col-span-1 space-y-8">
      <!-- Plan/Connection -->
      <section class="glass p-7 fade-in">
        <h2 class="text-lg font-bold text-[#203d9d] mb-4 tracking-wide">Subscription & Connection</h2>
        <?php if ($planNotActivated): ?>
          <div class="notification-glass mb-3">
            <i class="fas fa-exclamation-triangle mr-2 text-yellow-300"></i>
            <span><?= $planExpired ? 'Plan Expired' : 'Plan Not Activated' ?></span>
            <div class="mt-2 text-sm text-white/90">
              <?= $planExpired 
                ? 'Your subscription has expired. Please renew your plan to use WhatsApp connection features.' 
                : 'Your subscription plan is not active. Please activate your plan to access connection features.' 
              ?>
            </div>
            <a href="<?= $baseUrl ?>index#plans" class="inline-block mt-4 px-5 py-2 btn-cta shadow transition font-semibold">
              <i class="fas fa-crown mr-1"></i> View Plans
            </a>
          </div>
        <?php else: ?>
          <?php if ($daysLeft !== null): ?>
            <div class="notification-glass bg-gradient-to-r from-green-400/80 to-green-600/80 mb-4 text-white">
              <i class="fas fa-circle-check mr-2"></i> Plan Active
              <div class="mt-1 text-white/90 text-sm">
                <strong><?= $daysLeft ?></strong> day<?= $daysLeft != 1 ? 's' : '' ?> left (ends <strong><?= date('F j, Y', strtotime($planEndDate)) ?></strong>)
              </div>
            </div>
          <?php endif; ?>
          <div id="qr-container" class="hidden">
            <div class="mb-4">
              <h3 class="text-gray-600 mb-2 text-base font-semibold">Scan QR Code</h3>
              <div class="relative aspect-square bg-gradient-to-br from-[#e0e7ff] to-[#f4f6fb] rounded-lg overflow-hidden flex items-center justify-center">
                <div class="loader absolute inset-0 m-auto ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
                <img id="qr-img" class="w-full h-full object-contain hidden" src="" alt="QR Code" />
              </div>
            </div>
            <button onclick="window.location.reload()" class="w-full py-2 px-4 bg-[#4f46e5]/10 hover:bg-[#4f46e5]/20 text-[#203d9d] rounded-lg font-semibold transition-colors">
              Refresh Connection
            </button>
          </div>
          <button 
            id="delete-session-btn"
            class="w-full mt-5 px-6 py-2 btn-cta flex items-center gap-2 justify-center hidden shadow"
            type="button"
          >
            <i class="fas fa-trash-alt"></i>
            Delete WhatsApp Session
          </button>
          <div id="wa-status" class="text-center text-sm mt-3 text-[#4f46e5] font-semibold"></div>
          <!-- Modal for confirmation -->
          <div id="logout-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
            <div class="glass max-w-sm w-full p-7 relative shadow-2xl">
              <button id="close-modal-btn" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
              <div class="flex flex-col items-center">
                <div class="text-red-500 mb-2 text-3xl"><i class="fas fa-exclamation-triangle"></i></div>
                <h3 class="font-bold text-lg mb-2 text-red-700">Warning</h3>
                <p class="text-gray-700 mb-4 text-center">
                  Are you sure you want to <strong>delete your WhatsApp session</strong>?<br>
                  This will disconnect your WhatsApp account and remove the session from the server.
                </p>
                <div class="flex gap-4 w-full">
                  <button id="confirm-logout-btn" class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">
                    Yes, Delete
                  </button>
                  <button id="cancel-logout-btn" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold">
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </section>

     <!-- API Integration Card -->
<section class="glass p-7 fade-in">
  <h2 class="text-lg font-bold text-[#203d9d] mb-4 tracking-wide">API Integration</h2>
  <div class="space-y-7">
    <div>
      <div class="flex justify-between items-center mb-2">
        <span class="text-gray-600 font-medium">API Key</span>
        <?php if (!$planNotActivated): ?>
          <button id="get-api-key-btn"
                  class="btn-cta px-3 py-1 text-sm font-medium shadow"
                  type="button">
            <i class="fas fa-key mr-1"></i> Get API Key
          </button>
          <button id="copy-api-key-btn" class="copy-btn text-gray-400 hover:text-[#4f46e5] transition ml-3 hidden" aria-label="Copy API Key">
            <i class="fas fa-copy"></i>
          </button>
        <?php endif; ?>
      </div>
      <code id="api-key" class="text-[#4f46e5] bg-[#e0e7ff] px-3 py-2 rounded-lg block break-all font-semibold">
        <span id="api-key-placeholder" class="text-gray-400">Hidden – click "Get API Key"</span>
        <span id="api-key-value" style="display:none;"></span>
      </code>
    </div>
    <div>
      <div class="flex justify-between items-center mb-2">
        <span class="text-gray-600 font-medium">Endpoint</span>
        <?php if (!$planNotActivated): ?>
        <button onclick="copyToClipboard('api-endpoint')" class="copy-btn text-gray-400 hover:text-[#4f46e5] transition" aria-label="Copy API Endpoint">
          <i class="fas fa-copy"></i>
        </button>
        <?php endif; ?>
      </div>
      <code id="api-endpoint" class="text-[#203d9d] bg-[#e0e7ff] px-3 py-2 rounded-lg block break-all font-semibold">
        <?= (!$planNotActivated) ? $apiEndpoint : '<span class="text-gray-400">Not Available</span>' ?>
      </code>
    </div>
    <div class="mt-6 text-center">
      <a href="#" class="text-[#4f46e5] hover:text-[#203d9d] flex items-center justify-center font-semibold">
        <i class="fas fa-book mr-2"></i>
        View API Documentation
      </a>
    </div>
  </div>

  <!-- Password Modal -->
  <div id="api-key-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="glass max-w-xs w-full p-6 shadow-lg relative">
      <button id="close-api-key-modal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
      <h3 class="font-bold text-lg text-[#203d9d] mb-2">
        Verify Your Password
      </h3>
      <p class="text-gray-600 mb-4 text-sm">
        Enter your account password to reveal your API key.
      </p>
      <form id="api-key-form" class="flex flex-col gap-3">
        <input type="password" id="api-key-password" class="w-full p-2 border rounded border-[#e0e7ff] focus:border-[#4f46e5]" placeholder="Password" required autocomplete="current-password"/>
        <button type="submit" class="btn-cta py-2 shadow">Get API Key</button>
        <div id="api-key-error" class="text-red-600 font-semibold text-sm hidden"></div>
      </form>
    </div>
  </div>
</section>

      <!-- Support Card -->
      <section class="glass p-5 fade-in">
        <h2 class="text-lg font-semibold text-[#203d9d] mb-2">Need Help?</h2>
        <div class="flex items-center gap-3 text-gray-700 text-sm">
          <i class="fas fa-life-ring text-[#4f46e5]"></i>
          <span>Visit our <a href="#" class="underline hover:text-[#203d9d] font-semibold">support center</a> or <a href="#" class="underline hover:text-[#203d9d] font-semibold">documentation</a>.</span>
        </div>
      </section>
    </aside>

   <section class="lg:col-span-2 space-y-8 fade-in">
  <!-- Stats (NO PLAN QUOTA) -->
  <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-7">
    <div class="glass flex flex-col items-center justify-center p-7">
      <span class="text-gray-500 mb-2 text-sm stat-label">Messages Sent</span>
      <span class="stat-main font-bold text-[#203d9d]"><?= $messagesSent ?></span>
    </div>
    <div class="glass flex flex-col items-center justify-center p-7">
      <span class="text-gray-500 mb-2 text-sm stat-label">API Calls</span>
      <span class="stat-main font-bold text-[#203d9d]"><?= $apiCalls ?></span>
    </div>
  </section>


      <!-- Quick Test -->
  <section id="quick-test" class="glass p-10 fade-in">
    <h2 class="text-xl font-semibold text-[#203d9d] mb-4">Quick Test <span class="ml-1 text-[#4f46e5]"><i class="fa fa-bolt"></i></span></h2>
    <form method="POST" class="flex flex-col sm:flex-row gap-6 items-end">
      <div class="flex-1">
        <label class="text-gray-600 block mb-2 font-semibold">Test Number</label>
        <input type="tel" name="phone_number" 
          class="w-full p-4 bg-white/90 text-gray-700 rounded-lg border border-[#e0e7ff] focus:border-[#4f46e5] focus:ring-2 focus:ring-[#4f46e5]/50 transition text-lg font-semibold"
          placeholder="923000000000" required/>
      </div>
      <button type="submit"
        class="px-10 py-4 btn-cta rounded-lg flex items-center justify-center text-base font-bold transition h-[52px] shadow mt-4 sm:mt-0">
        <i class="fas fa-paper-plane mr-2"></i>Send Test
      </button>
    </form>
    <div id="message-status" class="p-4 rounded-lg bg-gradient-to-br from-[#e0e7ff] to-[#f4f6fb] mt-7 text-center font-medium">
      <?= $messageStatus ? 
          '<div class="flex items-center gap-2 text-sm justify-center">' . 
          (strpos($messageStatus, 'green') ? '' : '') .
          '<span>' . $messageStatus . '</span></div>' : 
          '<p class="text-gray-500">Test results will appear here</p>' ?>
    </div>
  </section>
</section>
  </main>
</body>
</html>