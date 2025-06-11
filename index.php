<?php
session_start();
require 'functions.php'; // Make sure this includes getSetting()

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? 'your_userid';

// Use base URL from settings table for all links and API endpoints
$base_url = rtrim(getSetting('base_url'), '/') . '/';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
   <link
        href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet"
    />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <style>
        .hero-bg {
            background: linear-gradient(120deg, #0f172a 0%, #2563eb 80%);
        }
        .feature-card {
            transition: all 0.19s cubic-bezier(.4, 0, .2, 1);
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
    <?php include("head.php"); ?>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 leading-relaxed">

    <?php include("header.php"); ?>

    <!-- Hero Section -->
    <section class="hero-bg text-white pt-16 pb-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
           

            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight drop-shadow">
                The Most Reliable <span class="text-indigo-200">WhatsApp Messaging</span> API
            </h1>
            <p class="text-base sm:text-lg md:text-xl mb-8 opacity-90 max-w-2xl mx-auto">
                Send unlimited messages for your business.<br />
                Secure, automated delivery for just
                <span class="font-semibold text-green-200">PKR 999/month</span>.
            </p>
            <?php
                $admin_number = getSetting('admin_number'); // E.g. 923251387814
                $wa_link = "https://wa.me/+" . $admin_number . "/?text=" . urlencode("Hello, I would like a WhatsApp API trial demo. Please assist.");
            ?>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a
                    href="<?= $wa_link ?>"
                    target="_blank"
                    class="w-full sm:w-auto px-8 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-lg shadow-lg text-lg transition"
                >
                    Start Free Trial
                </a>
                <a
                    href="#why-us"
                    class="w-full sm:w-auto px-8 py-3 border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-indigo-600 transition text-lg"
                >
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Why Us Section -->
    <section id="why-us" class="py-20 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-center mb-10">
                Why Choose This Platform?
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="feature-card bg-gray-50 dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow flex flex-col items-center text-center">
                    <div class="text-indigo-500 mb-4">
                        <i class="fas fa-shield-alt text-4xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Enterprise-Grade Security</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Your business messages are encrypted and sent directly to your customers through your verified WhatsApp number.
                    </p>
                </div>
                <div class="feature-card bg-gray-50 dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow flex flex-col items-center text-center">
                    <div class="text-indigo-500 mb-4">
                        <i class="fas fa-bolt text-4xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Unlimited Messaging</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        No per-message cost. Send as many as you want, any time, with no daily or monthly caps.
                    </p>
                </div>
                <div class="feature-card bg-gray-50 dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow flex flex-col items-center text-center">
                    <div class="text-indigo-500 mb-4">
                        <i class="fas fa-rocket text-4xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Effortless Integration</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        A simple REST API lets you connect your software, CRM, or website in minutes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-center mb-12">
                Perfect for Small Businesses
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="feature-card bg-white dark:bg-gray-700 p-6 sm:p-8 rounded-xl shadow-md flex flex-col items-center text-center">
                    <div class="text-indigo-600 mb-3">
                        <i class="fas fa-bullhorn text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">Promotions &amp; Updates</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Send offers or announcements to all your clients with a click.
                    </p>
                </div>
                <div class="feature-card bg-white dark:bg-gray-700 p-6 sm:p-8 rounded-xl shadow-md flex flex-col items-center text-center">
                    <div class="text-indigo-600 mb-3">
                        <i class="fas fa-bell text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">Automated Reminders</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Set up appointment, payment, or event reminders with zero hassle.
                    </p>
                </div>
                <div class="feature-card bg-white dark:bg-gray-700 p-6 sm:p-8 rounded-xl shadow-md flex flex-col items-center text-center">
                    <div class="text-indigo-600 mb-3">
                        <i class="fas fa-clock text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">Scheduled Messaging</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Plan your campaigns ahead—let the system handle timely delivery.
                    </p>
                </div>
                <div class="feature-card bg-white dark:bg-gray-700 p-6 sm:p-8 rounded-xl shadow-md flex flex-col items-center text-center">
                    <div class="text-indigo-600 mb-3">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-1">Analytics Dashboard</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Track delivery, see engagement, and get actionable insights.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Value Section -->
    <section id="plans" class="py-20 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-center mb-12">
                Affordable Bulk Messaging for Everyone
            </h2>
            <div class="flex flex-col items-center">
                <div class="bg-indigo-50 dark:bg-gray-800 rounded-2xl shadow-xl p-8 sm:p-12 text-center feature-card plan-popular max-w-md w-full">
                    <h3 class="text-xl sm:text-2xl font-bold mb-2 text-gray-900 dark:text-white">
                        Your Messaging API
                    </h3>
                    <p class="text-4xl sm:text-5xl font-extrabold text-indigo-700 mb-1">
                        PKR 999<span class="text-lg font-medium">/month</span>
                    </p>
                    <span class="text-sm sm:text-base text-gray-500 dark:text-gray-400">(Just $3/month)</span>
                    <ul class="mt-6 mb-8 space-y-2 text-gray-700 dark:text-gray-300 text-left mx-auto max-w-xs">
                        <li>✔ 1 WhatsApp Number</li>
                        <li>✔ Unlimited Bulk Messages</li>
                        <li>✔ Scheduled Campaigns</li>
                        <li>✔ Analytics Dashboard</li>
                        <li>✔ API Access</li>
                        <li>✔ Support via WhatsApp &amp; Email</li>
                    </ul>
                    <a
                        href="<?= $isLoggedIn ? $base_url . 'order?plan=basic' : $base_url . 'login?plan=basic' ?>"
                        class="inline-block w-full sm:w-auto px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition text-lg shadow"
                    >
                        Get Started for 999 PKR
                    </a>
                </div>
            </div>
            <p class="mt-10 text-center text-gray-500 dark:text-gray-400 text-base">
                No hidden fees. Cancel anytime. Unlimited API usage included.
            </p>
        </div>
    </section>

    <!-- API Integration Snippets -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-center mb-10 text-gray-900 dark:text-white">
                Easy Integration Examples
            </h2>
            <div class="space-y-8">
                <!-- PHP cURL -->
                <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow feature-card">
                    <div class="flex items-center mb-3 text-indigo-600">
                        <i class="fab fa-php text-2xl mr-2"></i>
                        <span class="font-semibold text-lg dark:text-white">PHP (cURL)</span>
                    </div>
                    <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto">
<code><?php echo htmlspecialchars('
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
'); ?></code>
                    </pre>
                </div>
                <!-- Node.js -->
                <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow feature-card">
                    <div class="flex items-center mb-3 text-green-600">
                        <i class="fab fa-node-js text-2xl mr-2"></i>
                        <span class="font-semibold text-lg dark:text-white">Node.js (axios)</span>
                    </div>
                    <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto">
<code><?php echo htmlspecialchars("
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
"); ?></code>
                    </pre>
                </div>
                <!-- Python -->
                <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow feature-card">
                    <div class="flex items-center mb-3 text-yellow-600">
                        <i class="fab fa-python text-2xl mr-2"></i>
                        <span class="font-semibold text-lg dark:text-white">Python (requests)</span>
                    </div>
                    <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto">
<code><?php echo htmlspecialchars("
import requests

resp = requests.post(
    '{$base_url}api/v1/users/{$userId}/send',
    json={'phonenumber': '+923xxxxxxxxx', 'message': 'Hello from Python!'},
    headers={'Authorization':'Bearer YOUR_API_KEY'}
)
print(resp.json())
"); ?></code>
                    </pre>
                </div>
            </div>
        </div>
    </section>

    <!-- Call-to-Action Section -->
    <section class="py-20 hero-bg text-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4">
                Upgrade Your Messaging Today
            </h2>
            <p class="mb-8 text-base sm:text-lg opacity-90 max-w-xl mx-auto">
                Try it risk-free. No credit card required. See the difference for yourself.
            </p>
<?php
                $admin_number = getSetting('admin_number'); // E.g. 923251387814
                $wa_link = "https://wa.me/+" . $admin_number . "/?text=" . urlencode("Hello, I would like a WhatsApp API trial demo. Please assist.");
            ?>
<a
    href="<?= $wa_link ?>"
    target="_blank"
    class="inline-block w-full sm:w-auto px-8 py-3 bg-white text-indigo-700 font-bold rounded-lg shadow-lg text-lg hover:bg-gray-200 transition"
>
    Start Free Trial
</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-6 md:mb-0 text-center md:text-left">
                <div class="text-2xl font-bold text-indigo-400">APIWhale</div>
                <p class="text-gray-400 mt-2">
                    Bulk WhatsApp for your business—affordable, reliable, unlimited.
                </p>
            </div>
            <div class="flex space-x-6">
                <a href="mailto:support@yourdomain.com" class="text-gray-400 hover:text-indigo-300">
                    <i class="fas fa-envelope text-xl"></i>
                </a>
                <a href="https://github.com/yourusername" class="text-gray-400 hover:text-indigo-300">
                    <i class="fab fa-github text-xl"></i>
                </a>
                <a href="https://twitter.com/yourusername" class="text-gray-400 hover:text-indigo-300">
                    <i class="fab fa-twitter text-xl"></i>
                </a>
                <a href="https://discord.gg/yourinvite" class="text-gray-400 hover:text-indigo-300">
                    <i class="fab fa-discord text-xl"></i>
                </a>
            </div>
        </div>
        <div class="mt-8 pt-6 border-t border-gray-800 text-center text-gray-400 text-sm">
            <p>&copy; <?= date('Y') ?> WhatsApp API Service. All rights reserved.</p>
        </div>
    </footer>

    <!-- Dark Mode Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const themeToggle = document.getElementById('theme-toggle');
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    document.documentElement.classList.toggle('dark');
                    localStorage.setItem(
                        'theme',
                        document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                    );
                });
            }
        });
    </script>
</body>
</html>
