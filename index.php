 <?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp API Service | Send Messages Automatically</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors">
    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        WhatsApp API
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                            Dashboard
                        </a>
                        <a href="logout.php" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 transition">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 transition">
                            Login
                        </a>
                        <a href="register.php" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                            Register
                        </a>
                    <?php endif; ?>
                    <button id="theme-toggle" class="p-2 rounded-full focus:outline-none">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-300"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Automate WhatsApp Messages with API</h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90">Send bulk messages, notifications, and more with our secure WhatsApp API service.</p>
            <div class="space-x-4">
                <a href="<?= $isLoggedIn ? 'dashboard.php' : 'register.php' ?>" 
                   class="px-8 py-3 bg-white text-indigo-600 font-bold rounded-lg hover:bg-gray-100 transition">
                    Get Started
                </a>
                <a href="#features" class="px-8 py-3 border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-indigo-600 transition">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">Key Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition">
                    <div class="text-indigo-600 dark:text-indigo-400 mb-4">
                        <i class="fas fa-bolt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Fast Delivery</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Send messages instantly to thousands of users with high speed.
                    </p>
                </div>
                <!-- Feature 2 -->
                <div class="feature-card bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition">
                    <div class="text-indigo-600 dark:text-indigo-400 mb-4">
                        <i class="fas fa-shield-alt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Secure API</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        End-to-end encrypted sessions with JWT authentication.
                    </p>
                </div>
                <!-- Feature 3 -->
                <div class="feature-card bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md transition">
                    <div class="text-indigo-600 dark:text-indigo-400 mb-4">
                        <i class="fas fa-qrcode text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Easy Setup</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Connect your WhatsApp in 2 minutes with QR code scanning.
                    </p>
                </div>
            </div>
        </div>
    </section>
  <!-- Features Section (unchanged) -->
  <section id="features" class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">Key Features</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- … three feature cards … -->
      </div>
    </div>
  </section>

  <!-- Pricing Section -->
  <section class="py-16 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">Pricing Plans</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Basic Plan -->
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg p-6 text-center">
          <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Basic</h3>
          <p class="text-4xl font-extrabold text-indigo-600 mb-1">$3<span class="text-lg font-medium">/mo</span></p>
          <span class="text-sm text-gray-500 dark:text-gray-300">(Approx. PKR 999)</span>
          <ul class="mt-4 mb-6 space-y-2 text-gray-600 dark:text-gray-300">
            <li>✔ Unlimited Messages</li>
            <li>✔ 1 WhatsApp Account</li>
            <li>❌ Media Sending</li>
          </ul>
          <a href="order.php?plan=basic" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Get Started
          </a>
        </div>

        <!-- Pro Plan -->
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-lg p-6 text-center border-2 border-indigo-600">
          <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Pro</h3>
          <p class="text-4xl font-extrabold text-indigo-600 mb-1">$7<span class="text-lg font-medium">/mo</span></p>
          <span class="text-sm text-gray-500 dark:text-gray-300">(Approx. PKR 2000)</span>
          <ul class="mt-4 mb-6 space-y-2 text-gray-600 dark:text-gray-300">
            <li>✔ Unlimited Messages</li>
            <li>✔ 2 WhatsApp Accounts</li>
            <li>✔ Media Sending</li>
          </ul>
          <a href="order.php?plan=pro" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Get Started
          </a>
        </div>

        <!-- Enterprise Plan -->
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg p-6 text-center">
          <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Enterprise</h3>
          <p class="text-4xl font-extrabold text-indigo-600 mb-1">$10<span class="text-lg font-medium">/mo</span></p>
          <span class="text-sm text-gray-500 dark:text-gray-300">(Approx. PKR 3000)</span>
          <ul class="mt-4 mb-6 space-y-2 text-gray-600 dark:text-gray-300">
            <li>✔ Unlimited Messages</li>
            <li>✔ 3 WhatsApp Accounts</li>
            <li>✔ Media Sending</li>
          </ul>
          <a href="order.php?plan=enterprise" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            Get Started
          </a>
        </div>

      </div>
    </div>
  </section>

  <!-- Integration Snippets -->
<section class="py-16 bg-gray-50 dark:bg-gray-800">
  <div class="max-w-4xl mx-auto px-4">
    <h2 class="text-3xl font-bold text-center mb-8 text-gray-900 dark:text-white">API Integration Examples</h2>

    <!-- PHP cURL -->
    <div class="mb-8 p-6 bg-white dark:bg-gray-900 rounded-lg shadow-lg">
      <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">PHP (cURL)</h3>
      <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg text-sm overflow-x-auto"><code><?php echo htmlspecialchars('
$ch = curl_init("https://yourdomain.com/api/send");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer YOUR_API_KEY",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "to" => "+923xxxxxxxxx",
    "message" => "Hello from PHP!"
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response;
'); ?></code></pre>
    </div>

    <!-- Node.js -->
    <div class="mb-8 p-6 bg-white dark:bg-gray-900 rounded-lg shadow-lg">
      <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Node.js (axios)</h3>
      <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg text-sm overflow-x-auto"><code>const axios = require('axios');

axios.post('https://yourdomain.com/api/send', {
  to: '+923xxxxxxxxx',
  message: 'Hello from Node.js!'
}, {
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
})
.then(res => console.log(res.data))
.catch(err => console.error(err));</code></pre>
    </div>

    <!-- Python -->
    <div class="p-6 bg-white dark:bg-gray-900 rounded-lg shadow-lg">
      <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Python (requests)</h3>
      <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg text-sm overflow-x-auto"><code>import requests

resp = requests.post(
    "https://yourdomain.com/api/send",
    json={"to":"+923xxxxxxxxx","message":"Hello from Python!"},
    headers={"Authorization":"Bearer YOUR_API_KEY"}
)
print(resp.json())</code></pre>
    </div>
  </div>
</section>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-8">
    <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
      <div class="mb-4 md:mb-0">
        <div class="text-2xl font-bold text-indigo-400">WhatsApp API</div>
        <p class="text-gray-400 mt-2">Automate your WhatsApp messages securely.</p>
      </div>
      <div class="flex space-x-6">
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-github text-xl"></i></a>
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-discord text-xl"></i></a>
      </div>
    </div>
    <div class="mt-8 pt-8 border-t border-gray-700 text-center text-gray-400">
      <p>&copy; <?= date('Y') ?> WhatsApp API Service. All rights reserved.</p>
    </div>
  </footer>

  <!-- Dark Mode Toggle -->
  <script>
    const themeToggle = document.getElementById('theme-toggle');
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
    themeToggle.addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });
  </script>
</body>
</html>
