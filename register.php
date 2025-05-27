<?php
require 'functions.php';
session_start();

// Capture plan from GET (when redirected from Pricing) or POST (after form submit)
$plan = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['plan'])) {
    $plan = filter_var($_GET['plan'], FILTER_SANITIZE_STRING);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $plan = filter_var($_POST['plan'], FILTER_SANITIZE_STRING);
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim and sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $userId = "user_" . uniqid();

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please provide a valid email address.";
    } elseif (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errorMsg = "Username must be at least 3 characters and contain only letters, numbers, or underscores.";
    } elseif (strlen($password) < 6) {
        $errorMsg = "Password must be at least 6 characters long.";
    } elseif (registerUser($username, $password, $email, $userId)) {
        // Auto-login after successful registration
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        // Redirect to order page if plan set, else dashboard
        if (!empty($plan)) {
            header("Location: order.php?plan=" . urlencode($plan));
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $errorMsg = "Registration failed! Username or email may already exist.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - WhatsApp API Service</title>
  <meta name="description" content="Create your WhatsApp API account to send automated and secure messages. Start your free trial now.">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-100 to-blue-50 min-h-screen flex flex-col">

  <!-- Navbar -->
  <nav class="bg-white shadow py-4">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold text-blue-700 tracking-tight">WhatsApp API</a>
      <div class="space-x-4">
        <a href="login.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="text-blue-700 font-medium">Login</a>
        <a href="register.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800 transition">Register</a>
      </div>
    </div>
  </nav>

  <!-- Registration Form Section -->
  <main class="flex-1 flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
      <h2 class="text-2xl font-bold text-center mb-6 text-blue-700">Create Your Account</h2>

      <?php if ($errorMsg): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-center"><?= $errorMsg ?></div>
      <?php endif; ?>

      <form method="POST" class="space-y-6" autocomplete="off">
        <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">

        <div>
          <label for="username" class="block mb-1 text-sm font-semibold text-gray-700">Username</label>
          <input type="text" id="username" name="username" required minlength="3" pattern="[a-zA-Z0-9_]+"
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                 autocomplete="username">
        </div>

        <div>
          <label for="email" class="block mb-1 text-sm font-semibold text-gray-700">Email</label>
          <input type="email" id="email" name="email" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                 autocomplete="email">
        </div>

        <div>
          <label for="password" class="block mb-1 text-sm font-semibold text-gray-700">Password</label>
          <input type="password" id="password" name="password" required minlength="6"
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                 autocomplete="new-password">
        </div>

        <button type="submit"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white py-2.5 rounded-lg text-lg font-semibold shadow transition">
          Register
        </button>
      </form>

      <p class="text-center mt-6 text-sm text-gray-600">
        Already have an account?
        <a href="login.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="text-blue-700 hover:underline font-semibold">
          Login here
        </a>
      </p>
    </div>
  </main>

  <footer class="bg-white border-t border-gray-200 py-4 mt-8 text-center text-gray-400 text-sm">
    &copy; <?= date('Y') ?> WhatsApp API Service. All rights reserved.
  </footer>
</body>
</html>