<?php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$username || !$password) {
        $error = "Username and password are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if admin already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn()) {
            $error = "Admin username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hash])) {
                $success = "Admin user created. You should now <a href='login.php' class='underline text-blue-700'>login</a>.";
            } else {
                $error = "Failed to create admin user.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Admin User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-indigo-700 text-center">Create Admin User</h2>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= $success ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Username</label>
                <input type="text" name="username" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 mb-1">Confirm Password</label>
                <input type="password" name="confirm" class="w-full border px-3 py-2 rounded" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 font-semibold">
                Create Admin
            </button>
        </form>
    </div>
</body>
</html>