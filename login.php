<?php
require 'functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize inputs
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

    // Try to login
    $user = loginUser($username, $password);

    if ($user) {
        // Store only necessary user details in session
        $_SESSION['user_id'] = $user['user_id']; // Store unique user_id
        $_SESSION['username'] = $user['username']; // Store username
        
        header('Location: dashboard.php'); // Redirect to dashboard
        exit();
    } else {
        echo "<p class='text-red-500 text-center mt-4'>Invalid login credentials!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md p-8 space-y-6 bg-gray-800 rounded-lg shadow-lg">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-indigo-400">Login</h1>
      <p class="mt-2 text-gray-300">Please sign in to your account</p>
    </div>
    <form method="POST" class="space-y-4">
      <div>
        <label for="username" class="block text-gray-300">Username</label>
        <input type="text" name="username" id="username" placeholder="Enter your username" required class="w-full px-4 py-2 mt-1 bg-gray-700 border border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-100">
      </div>
      <div>
        <label for="password" class="block text-gray-300">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required class="w-full px-4 py-2 mt-1 bg-gray-700 border border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-100">
      </div>
      <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-500 rounded text-white font-medium transition duration-150">Login</button>
    </form>
    <div class="text-center">
      <p class="text-gray-400">Don't have an account? <a href="register.php" class="text-indigo-400 hover:underline">Register here</a></p>
    </div>
  </div>
</body>
</html>