<?php
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs as needed
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email    = $_POST['email'];

    // Generate a unique user ID (example: user_123456)
    $userId = "user_" . uniqid();

    if (registerUser($username, $password, $email, $userId)) {
        echo "<div class='text-center mt-4 text-green-500'>Registration successful! Your User ID is: " . $userId . "</div>";
    } else {
        echo "<div class='text-center mt-4 text-red-500'>Registration failed!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md p-8 space-y-6 bg-gray-800 rounded-lg shadow-lg">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-indigo-400">Register</h1>
      <p class="mt-2 text-gray-300">Create a new account</p>
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
      <div>
        <label for="email" class="block text-gray-300">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required class="w-full px-4 py-2 mt-1 bg-gray-700 border border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-100">
      </div>
      <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-500 rounded text-white font-medium transition duration-150">Register</button>
    </form>
    <div class="text-center">
      <p class="text-gray-400">Already have an account? <a href="login.php" class="text-indigo-400 hover:underline">Login here</a></p>
    </div>
  </div>
</body>
</html>