<?php
require 'functions.php';
session_start();

// Handle the plan redirection
$plan = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['plan'])) {
    $plan = htmlspecialchars($_GET['plan']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $plan = htmlspecialchars($_POST['plan']);
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    $user = loginUser($username, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        if (!empty($plan)) {
            header("Location: order?plan=" . urlencode($plan));
        } else {
            header("Location: dashboard");
        }
        exit();
    } else {
        $errorMsg = "Invalid login credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - WhatsApp API</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">

  <!-- Navbar -->
  <nav class="bg-white shadow-md py-4">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
      <a href="index" class="text-xl font-bold text-blue-600">WhatsApp API</a>
      <div class="space-x-4">
        <a href="login" class="text-blue-600 font-medium">Login</a>
        <a href="register" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Register</a>
      </div>
    </div>
  </nav>

  <!-- Login Form Section -->
  <section class="py-16">
    <div class="max-w-md mx-auto bg-white p-8 rounded shadow-md">
      <h2 class="text-2xl font-semibold text-center mb-6 text-blue-700">Login to Your Account</h2>
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
          <label for="password" class="block mb-1 text-sm font-medium">Password</label>
          <input type="password" id="password" name="password" required
                 class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
          Login
        </button>
      </form>

      <p class="text-center mt-4 text-sm">
        Don't have an account?
        <a href="register<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="text-blue-600 hover:underline">Register here</a>
      </p>
    </div>
  </section>


</body>
</html>
