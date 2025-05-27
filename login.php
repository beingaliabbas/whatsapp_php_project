<?php
require 'functions.php';
session_start();

// Handle plan redirection
$plan = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['plan'])) {
    $plan = htmlspecialchars($_GET['plan']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $plan = htmlspecialchars($_POST['plan']);
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = loginUser($username, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: " . (!empty($plan) ? "order?plan=" . urlencode($plan) : "dashboard"));
        exit();
    } else {
        $errorMsg = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - WhatsApp API Service</title>
  <meta name="description" content="Sign in to manage your WhatsApp API account. Access secure messaging tools and developer dashboard.">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-100 to-blue-50 min-h-screen flex flex-col">

  <!-- Navbar -->
  <nav class="bg-white shadow py-4">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <a href="index" class="text-2xl font-bold text-blue-700 tracking-tight">WhatsApp API</a>
      <div class="space-x-4">
        <a href="login" class="text-blue-700 font-medium">Login</a>
        <a href="register" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800 transition">Register</a>
      </div>
    </div>
  </nav>

  <!-- Login Form Section -->
  <main class="flex-1 flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
      <h2 class="text-2xl font-bold text-center mb-6 text-blue-700">Sign in to Your Account</h2>
      <?php if ($errorMsg): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-center"><?= $errorMsg ?></div>
      <?php endif; ?>
      <form method="POST" class="space-y-6" autocomplete="off">
        <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">

        <div>
          <label for="username" class="block mb-1 text-sm font-semibold text-gray-700">Username</label>
          <input type="text" id="username" name="username" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                 autocomplete="username">
        </div>

        <div>
          <label for="password" class="block mb-1 text-sm font-semibold text-gray-700">Password</label>
          <input type="password" id="password" name="password" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                 autocomplete="current-password">
        </div>

        <button type="submit"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white py-2.5 rounded-lg text-lg font-semibold shadow transition">
          Login
        </button>
      </form>

      <p class="text-center mt-6 text-sm text-gray-600">
        Don't have an account?
        <a href="register<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="text-blue-700 hover:underline font-semibold">Register here</a>
      </p>
    </div>
  </main>

  <footer class="bg-white border-t border-gray-200 py-4 mt-8 text-center text-gray-400 text-sm">
    &copy; <?= date('Y') ?> WhatsApp API Service. All rights reserved.
  </footer>
</body>
</html>