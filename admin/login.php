<?php
session_start();
require_once 'db.php';
require_once '../whatsapp_admin_send.php'; // Make sure this is the correct path

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';
$show_otp = false;
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$otp_submitted = trim($_POST['otp'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['password']) && !$otp_submitted) {
        // Step 1: Username & password submitted, check credentials
        if ($username && $password) {
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                // Send OTP to WhatsApp
                $otp = rand(100000, 999999);
                $_SESSION['admin_otp'] = $otp;
                $_SESSION['admin_username_temp'] = $admin['username'];
                $_SESSION['admin_userid_temp'] = $admin['id'];
                $_SESSION['admin_whatsapp_temp'] = $admin['whatsapp'];

                // WhatsApp number must be in international format, e.g., 923001234567
                $wa_number = preg_replace('/\D/', '', $admin['whatsapp']);
                $wa_message = "Your WhatsApp API admin login code is: $otp";

                $wa_result = send_whatsapp_message($wa_number, $wa_message);

                // FIX: check result array, not strict boolean
                if (isset($wa_result['success']) && $wa_result['success'] === true) {
                    $show_otp = true;
                    $success = "A verification code has been sent to your WhatsApp number.";
                } else {
                    $error = "Failed to send WhatsApp verification code. Please contact support.";
                    if (isset($wa_result['error'])) {
                        $error .= ' Details: ' . $wa_result['error'];
                    }
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Both fields are required.";
        }
    } elseif ($otp_submitted && isset($_SESSION['admin_otp'])) {
        // Step 2: OTP submitted
        if ($otp_submitted == $_SESSION['admin_otp']) {
            // OTP matches, log in
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $_SESSION['admin_username_temp'];
            // Clean up
            unset($_SESSION['admin_otp'], $_SESSION['admin_username_temp'], $_SESSION['admin_userid_temp'], $_SESSION['admin_whatsapp_temp']);
            header("Location: index.php");
            exit;
        } else {
            $show_otp = true;
            $error = "Invalid verification code.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Login - WhatsApp API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-indigo-700 text-center">Admin Login</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!$show_otp): ?>
        <form method="post" autocomplete="off">
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" class="w-full border px-3 py-2 rounded" required autofocus>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full border px-3 py-2 rounded" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 font-semibold">
                Login
            </button>
        </form>
        <?php else: ?>
        <form method="post" autocomplete="off">
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Verification Code (OTP)</label>
                <input type="text" name="otp" class="w-full border px-3 py-2 rounded" required maxlength="6" pattern="\d{6}" autofocus>
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 font-semibold">
                Verify & Login
            </button>
        </form>
        <form method="post" class="mt-2" autocomplete="off">
            <!-- Resend OTP fields -->
            <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
            <input type="hidden" name="password" value="<?= htmlspecialchars($password) ?>">
            <button type="submit" class="w-full bg-indigo-100 text-indigo-700 py-2 rounded hover:bg-indigo-200 font-semibold">
                Resend Code
            </button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>