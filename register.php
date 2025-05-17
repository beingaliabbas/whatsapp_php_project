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
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $email    = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $userId   = "user_" . uniqid(); // example unique user ID

    if (!$email) {
        $errorMsg = "Please provide a valid email address.";
    } elseif (registerUser($username, $password, $email, $userId)) {
        // Auto-login after successful registration
        $_SESSION['user_id']  = $userId;
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
  <title>Register - WhatsApp API</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">

  <!-- Navbar -->
  <nav class="bg-white shadow-md py-4">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
      <a href="index.php" class="text-xl font-bold text-blue-600">WhatsApp API</a>
      <div class="space-x-4">
        <a href="login.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="text-blue-600 font-medium">Login</a>
        <a href="register.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Register</a>
      </div>
    </div>
  </nav>

  <!-- Registration Form Section -->
  <section class="py-16">
    <div class="max-w-md mx-auto bg-white p-8 rounded shadow-md">
      <h2 class="text-2xl font-semibold text-center mb-6 text-blue-700">Create Your Account</h2>

      <?php if ($errorMsg): ?>
        <p class="text-red-500 text-center mb-4"><?= $errorMsg ?></p>
      <?php endif; ?>

      <form method="POST" class="space-y-5">
        <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">

        <div>
          <label for="username" class="block mb-1 text-sm font-medium">Username</label>
          <input type="text" id="username" name="username" required
                 class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
          <label for="email" class="block mb-1 text-sm font-medium">Email</label>
          <input type="email" id="email" name="email" required
                 class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
          <label for="password" class="block mb-1 text-sm font-medium">Password</label>
          <input type="password" id="password" name="password" required
                 class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
          Register
        </button>
      </form>

      <p class="text-center mt-4 text-sm">
        Already have an account?
        <a href="login.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="text-blue-600 hover:underline">
          Login here
        </a>
      </p>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-white border-t mt-16">
    <div class="max-w-7xl mx-auto py-4 px-4 text-center text-sm text-gray-600">
      &copy; <?= date('Y') ?> WhatsApp API Service. All rights reserved.
    </div>
  </footer>

</body>
</html>
