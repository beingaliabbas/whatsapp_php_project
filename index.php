<?php
session_start();
require 'functions.php'; // Make sure this includes getSetting()

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? 'your_userid';

// Use base URL from settings table for all links and API endpoints
$base_url = rtrim(getSetting('base_url'), '/') . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Business API | Reliable Messaging Automation</title>
    <meta name="description" content="Send WhatsApp messages automatically and securely. Boost your business with our developer-friendly API—trusted, compliant, and scalable.">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-bg {
            background: linear-gradient(120deg, #0f172a 0%, #2563eb 80%);
        }
        .feature-card {
            transition: all 0.19s cubic-bezier(.4,0,.2,1);
        }
        .feature-card:hover {
            transform: translateY(-4px) scale(1.012);
            box-shadow: 0 10px 32px rgba(37, 99, 235, 0.14);
        }
        .plan-popular {
            border-width: 3px;
            border-color: #2563eb;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 leading-relaxed">

<?php include("header.php");?> 

<!-- Hero Section -->
<section class="hero-bg text-white py-20 md:py-28">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-7 tracking-tight leading-tight drop-shadow">
            Powerful WhatsApp <span class="text-indigo-300">Business Messaging.</span>
        </h1>
        <p class="text-lg sm:text-xl md:text-2xl mb-8 opacity-90">
            Automate notifications, reach clients instantly, and scale your business with the most developer-friendly and secure WhatsApp API platform.
        </p>
        <div class="flex flex-col md:flex-row justify-center gap-4">
            <a href="<?= $isLoggedIn ? $base_url . 'account' : $base_url . 'register' ?>" 
               class="px-10 py-4 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg text-lg transition">
                Start Free Trial
            </a>
            <a href="#why-us" class="px-10 py-4 border-2 border-white text-white font-bold rounded-xl hover:bg-white hover:text-indigo-600 transition text-lg">
                Why Choose Us?
            </a>
        </div>
    </div>
</section>

<!-- Why Us (Trust) Section -->
<section id="why-us" class="py-20 bg-white dark:bg-gray-900">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-10 text-gray-900 dark:text-white">Why Businesses Trust Us?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl shadow feature-card transition flex flex-col items-center">
                <div class="text-indigo-500 mb-3"><i class="fas fa-shield-alt text-3xl"></i></div>
                <h3 class="font-bold text-xl mb-2 dark:text-white">Enterprise Security</h3>
                <p class="text-gray-600 dark:text-gray-300 text-center">End-to-end encrypted, GDPR compliant, token-based access. All communication is secure and privacy-first.</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl shadow feature-card transition flex flex-col items-center">
                <div class="text-indigo-500 mb-3"><i class="fas fa-rocket text-3xl"></i></div>
                <h3 class="font-bold text-xl mb-2 dark:text-white">Lightning-Fast Delivery</h3>
                <p class="text-gray-600 dark:text-gray-300 text-center">99.99% uptime SLA. Cloud infrastructure ensures real-time delivery for all notifications.</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl shadow feature-card transition flex flex-col items-center">
                <div class="text-indigo-500 mb-3"><i class="fas fa-code text-3xl"></i></div>
                <h3 class="font-bold text-xl mb-2 dark:text-white">Developer-First</h3>
                <p class="text-gray-600 dark:text-gray-300 text-center">Easy onboarding, QR integration, robust docs, and 24/7 support for your stack.</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 bg-gray-50 dark:bg-gray-800">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">All the Features You Need</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="feature-card bg-white dark:bg-gray-700 p-6 rounded-xl shadow-md transition flex flex-col items-center">
                <div class="text-indigo-600 mb-2"><i class="fas fa-bolt text-2xl"></i></div>
                <h3 class="font-bold text-lg mb-1 dark:text-white">Bulk Messaging</h3>
                <p class="text-gray-600 dark:text-gray-300 text-center">Send thousands per minute with delivery tracking and smart retries.</p>
            </div>
            <div class="feature-card bg-white dark:bg-gray-700 p-6 rounded-xl shadow-md transition flex flex-col items-center">
                <div class="text-indigo-600 mb-2"><i class="fas fa-lock text-2xl"></i></div>
                <h3 class="font-bold text-lg mb-1 dark:text-white">Secure Authentication</h3>
                <p class="text-gray-600 dark:text-gray-300 text-center">JWT tokens &amp; API keys for secure access. Role-based permissions for teams.</p>
            </div>
            <div class="feature-card bg-white dark:bg-gray-700 p-6 rounded-xl shadow-md transition flex flex-col items-center">
                <div class="text-indigo-600 mb-2"><i class="fas fa-qrcode text-2xl"></i></div>
                <h3 class="font-bold text-lg mb-1 dark:text-white">Instant QR Onboarding</h3>
                <p class="text-gray-600 dark:text-gray-300 text-center">Connect WhatsApp in seconds. No complex setup, just scan and send.</p>
            </div>
            <div class="feature-card bg-white dark:bg-gray-700 p-6 rounded-xl shadow-md transition flex flex-col items-center">
                <div class="text-indigo-600 mb-2"><i class="fas fa-chart-line text-2xl"></i></div>
                <h3 class="font-bold text-lg mb-1 dark:text-white">Dashboard &amp; Analytics</h3>
                <p class="text-gray-600 dark:text-gray-300 text-center">Monitor usage, delivery, and engagement. Export logs and get insights.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Plans -->
<section id="plans" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900">Transparent Pricing</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Basic -->
            <div class="bg-gray-50 rounded-2xl shadow-lg p-8 text-center feature-card">
                <h3 class="text-xl font-bold mb-2 text-gray-900">Basic</h3>
                <p class="text-4xl font-extrabold text-indigo-600 mb-2">$3<span class="text-lg font-medium">/mo</span></p>
                <span class="text-sm text-gray-500">(PKR 999)</span>
                <ul class="mt-6 mb-8 space-y-2 text-gray-700">
                    <li>✔ 1 WhatsApp Number</li>
                    <li>✔ Unlimited Messages</li>
                    <li>❌ Media Sending</li>
                    <li>❌ Priority Support</li>
                </ul>
                <a href="<?= $isLoggedIn ? $base_url . 'order?plan=basic' : $base_url . 'login?plan=basic' ?>" class="inline-block px-7 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Get Started
                </a>
            </div>
            <!-- Pro -->
            <div class="bg-white rounded-2xl shadow-xl p-10 text-center feature-card plan-popular relative z-10">
                <span class="absolute -top-6 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-semibold px-4 py-1 rounded-full shadow">Most Popular</span>
                <h3 class="text-xl font-bold mb-2 text-gray-900">Pro</h3>
                <p class="text-4xl font-extrabold text-indigo-600 mb-2">$7<span class="text-lg font-medium">/mo</span></p>
                <span class="text-sm text-gray-500">(PKR 2000)</span>
                <ul class="mt-6 mb-8 space-y-2 text-gray-700">
                    <li>✔ 2 WhatsApp Numbers</li>
                    <li>✔ Unlimited Messages</li>
                    <li>✔ Media Sending</li>
                    <li>✔ Priority Support</li>
                </ul>
                <a href="<?= $isLoggedIn ? $base_url . 'order?plan=pro' : $base_url . 'login?plan=pro' ?>" class="inline-block px-7 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Get Started
                </a>
            </div>
            <!-- Enterprise -->
            <div class="bg-gray-50 rounded-2xl shadow-lg p-8 text-center feature-card">
                <h3 class="text-xl font-bold mb-2 text-gray-900">Enterprise</h3>
                <p class="text-4xl font-extrabold text-indigo-600 mb-2">$10<span class="text-lg font-medium">/mo</span></p>
                <span class="text-sm text-gray-500">(PKR 3000)</span>
                <ul class="mt-6 mb-8 space-y-2 text-gray-700">
                    <li>✔ 3 WhatsApp Numbers</li>
                    <li>✔ Unlimited Messages</li>
                    <li>✔ Media Sending</li>
                    <li>✔ Priority & Onboarding Support</li>
                </ul>
                <a href="<?= $isLoggedIn ? $base_url . 'order?plan=enterprise' : $base_url . 'login?plan=enterprise' ?>" class="inline-block px-7 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Get Started
                </a>
            </div>
        </div>
        <p class="mt-10 text-center text-gray-500 text-sm">No setup fees. Cancel anytime. All plans include unlimited API requests and analytics.</p>
    </div>
</section>

<!-- API Integration Snippets -->
<section class="py-20 bg-gray-50 dark:bg-gray-800">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-10 text-gray-900 dark:text-white">Integration Examples</h2>
        <div class="space-y-8">
            <!-- PHP cURL -->
            <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow feature-card">
                <div class="flex items-center mb-3 text-indigo-600"><i class="fab fa-php text-2xl mr-2"></i><span class="font-semibold text-lg dark:text-white">PHP (cURL)</span></div>
                <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"><code><?php echo htmlspecialchars('
$ch = curl_init("' . $base_url . 'api/v1/users/'.$userId.'/send");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer YOUR_API_KEY",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "phonenumber" => "+923xxxxxxxxx",
    "message" => "Hello from PHP!"
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response;
'); ?></code></pre>
            </div>
            <!-- Node.js -->
            <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow feature-card">
                <div class="flex items-center mb-3 text-green-600"><i class="fab fa-node-js text-2xl mr-2"></i><span class="font-semibold text-lg dark:text-white">Node.js (axios)</span></div>
                <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"><code><?php echo htmlspecialchars("
const axios = require('axios');

axios.post('{$base_url}api/v1/users/{$userId}/send', {
  phonenumber: '+923xxxxxxxxx',
  message: 'Hello from Node.js!'
}, {
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
})
.then(res => console.log(res.data))
.catch(err => console.error(err));
"); ?></code></pre>
            </div>
            <!-- Python -->
            <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow feature-card">
                <div class="flex items-center mb-3 text-yellow-600"><i class="fab fa-python text-2xl mr-2"></i><span class="font-semibold text-lg dark:text-white">Python (requests)</span></div>
                <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"><code><?php echo htmlspecialchars("
import requests

resp = requests.post(
    '{$base_url}api/v1/users/{$userId}/send',
    json={'phonenumber': '+923xxxxxxxxx', 'message': 'Hello from Python!'},
    headers={'Authorization':'Bearer YOUR_API_KEY'}
)
print(resp.json())
"); ?></code></pre>
            </div>
        </div>
    </div>
</section>

<!-- Call-to-Action Section -->
<section class="py-20 hero-bg text-white text-center">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Supercharge Your Communication?</h2>
        <p class="mb-8 text-lg opacity-90">Start your free trial—no credit card required. Experience enterprise WhatsApp messaging now.</p>
        <a href="<?= $isLoggedIn ? $base_url . 'account' : $base_url . 'register' ?>" class="px-10 py-4 bg-white text-indigo-700 font-bold rounded-lg shadow-lg text-lg hover:bg-gray-200 transition">
            Start Free Trial
        </a>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-10">
    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
        <div class="mb-4 md:mb-0">
            <div class="text-2xl font-bold text-indigo-400">WhatsApp API</div>
            <p class="text-gray-400 mt-2">Automate WhatsApp messages—securely, reliably, and at scale.</p>
        </div>
        <div class="flex space-x-6">
            <a href="mailto:support@yourdomain.com" class="text-gray-400 hover:text-indigo-300"><i class="fas fa-envelope text-xl"></i></a>
            <a href="https://github.com/yourusername" class="text-gray-400 hover:text-indigo-300"><i class="fab fa-github text-xl"></i></a>
            <a href="https://twitter.com/yourusername" class="text-gray-400 hover:text-indigo-300"><i class="fab fa-twitter text-xl"></i></a>
            <a href="https://discord.gg/yourinvite" class="text-gray-400 hover:text-indigo-300"><i class="fab fa-discord text-xl"></i></a>
        </div>
    </div>
    <div class="mt-8 pt-6 border-t border-gray-800 text-center text-gray-400 text-sm">
        <p>&copy; <?= date('Y') ?> WhatsApp API Service. All rights reserved.</p>
    </div>
</footer>

<!-- Dark Mode Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        });
    }
});
</script>
</body>
</html>