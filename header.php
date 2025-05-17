<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn && isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>

<nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="text-2xl font-bold text-indigo-600">
                    WhatsApp API
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <?php if ($isLoggedIn): ?>
                    <span class="text-gray-700 font-medium">
                        Hello, <?= htmlspecialchars($username) ?>
                    </span>
                    <a href="dashboard" class="no-underline px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        Dashboard
                    </a>
                    <a href="logout" class="no-underline px-4 py-2 text-gray-600 hover:text-indigo-600 transition">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="login" class="no-underline px-4 py-2 text-indigo-600 hover:text-indigo-800 transition">
                        Login
                    </a>
                    <a href="register" class="no-underline px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        Register
                    </a>
                <?php endif; ?>
                <button id="theme-toggle" class="p-2 rounded-full focus:outline-none hidden">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>
