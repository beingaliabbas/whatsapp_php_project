<?php
require 'functions.php';
session_start();

// Capture plan if passed via GET or POST
$plan = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['plan'])) {
    $plan = filter_var($_GET['plan'], FILTER_SANITIZE_STRING);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $plan = filter_var($_POST['plan'], FILTER_SANITIZE_STRING);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register – WhatsApp API Service</title>
  <meta name="description" content="Create your WhatsApp API account to send automated and secure messages. Start your free trial now.">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .form-input {
      width: 100%;
      padding: 0.5rem 1rem;
      border: 1px solid #d1d5db;
      border-radius: 0.5rem;
      outline: none;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.3);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-blue-50 min-h-screen flex flex-col">

  <!-- Navbar -->
  <nav class="bg-white shadow py-4">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold text-blue-700">WhatsApp API</a>
      <div class="space-x-4">
        <a href="login.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="text-blue-700">Login</a>
        <a href="register.php<?= $plan ? '?plan=' . urlencode($plan) : '' ?>" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800">Register</a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="flex-1 flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
      <h2 class="text-2xl font-bold text-center mb-6 text-blue-700">Create Your Account</h2>

      <!-- Loader & Error -->
      <div id="formLoader" class="text-center mb-4" style="display:none;">
        <span class="text-blue-700 font-bold">Processing...</span>
      </div>
      <div id="errorMsg" class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-center" style="display:none;"></div>

      <!-- STEP 1: Registration Fields -->
      <form id="step1" class="space-y-6" autocomplete="off">
        <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">

        <div>
          <label for="fullname" class="block mb-1 text-sm font-semibold text-gray-700">Full Name</label>
          <input type="text" id="fullname" name="fullname" required minlength="3" pattern="[\p{L} \'-]+"
                 class="form-input" autocomplete="name">
        </div>

        <div>
          <label for="whatsapp" class="block mb-1 text-sm font-semibold text-gray-700">WhatsApp Number</label>
          <input type="tel" id="whatsapp" name="whatsapp" required pattern="\d{10,15}"
                 class="form-input" autocomplete="tel" placeholder="e.g. 923001234567">
          <span class="block text-xs text-gray-500 mt-1">Digits only, no + sign</span>
        </div>

        <div>
          <label for="username" class="block mb-1 text-sm font-semibold text-gray-700">Username</label>
          <input type="text" id="username" name="username" required minlength="3" pattern="[a-zA-Z0-9_]+"
                 class="form-input" autocomplete="username">
        </div>

        <div>
          <label for="email" class="block mb-1 text-sm font-semibold text-gray-700">Email</label>
          <input type="email" id="email" name="email" required class="form-input" autocomplete="email">
        </div>

        <div>
          <label for="password" class="block mb-1 text-sm font-semibold text-gray-700">Password</label>
          <input type="password" id="password" name="password" required minlength="6"
                 class="form-input" autocomplete="new-password">
          <div id="passwordStrength" class="text-sm mt-1"></div>
        </div>

        <button type="submit"
                id="sendOtpBtn"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white py-2.5 rounded-lg text-lg font-semibold shadow transition opacity-50 cursor-not-allowed"
                disabled>
          Send OTP
        </button>
      </form>

      <!-- STEP 2: OTP Verification -->
      <form id="step2" class="space-y-6" style="display:none;" autocomplete="off">
        <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">
        <div>
          <label for="otp" class="block mb-1 text-sm font-semibold text-gray-700">Enter OTP</label>
          <input type="text" id="otp" name="otp" required pattern="\d{6}" maxlength="6"
                 class="form-input" placeholder="123456">
        </div>
        <button type="submit"
                id="verifyOtpBtn"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-lg font-semibold shadow transition">
          Verify & Register
        </button>
      </form>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 py-4 text-center text-gray-400 text-sm">
    &copy; <?= date('Y') ?> WhatsApp API Service. All rights reserved.
  </footer>

  <script>
    const step1        = document.getElementById('step1');
    const step2        = document.getElementById('step2');
    const sendOtpBtn   = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const inputsStep1  = step1.querySelectorAll('input');
    const passwordInput= document.getElementById('password');
    const strengthDisp = document.getElementById('passwordStrength');
    const loader       = document.getElementById('formLoader');
    const errorDiv     = document.getElementById('errorMsg');

    // Password strength meter
    function checkPasswordStrength(pw) {
      const strong = /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}/;
      const medium = /(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}/;
      let status = 'Weak';
      strengthDisp.style.color = 'red';
      if (strong.test(pw)) {
        status = 'Strong';
        strengthDisp.style.color = 'green';
      } else if (medium.test(pw)) {
        status = 'Medium';
        strengthDisp.style.color = 'orange';
      }
      strengthDisp.textContent = `Password Strength: ${status}`;
      return status !== 'Weak';
    }

    // Enable Send OTP button only when step1 is valid
    function toggleSendOtp() {
      const valid = [...inputsStep1].every(i => i.checkValidity()) &&
                    checkPasswordStrength(passwordInput.value);
      if (valid) {
        sendOtpBtn.disabled = false;
        sendOtpBtn.classList.remove('opacity-50','cursor-not-allowed');
      } else {
        sendOtpBtn.disabled = true;
        sendOtpBtn.classList.add('opacity-50','cursor-not-allowed');
      }
    }
    inputsStep1.forEach(i => i.addEventListener('input', toggleSendOtp));
    passwordInput.addEventListener('input', toggleSendOtp);

    // Utility to display errors
    function showError(msg) {
      loader.style.display = 'none';
      errorDiv.textContent = msg;
      errorDiv.style.display = 'block';
    }

    // STEP 1: Send OTP
    step1.addEventListener('submit', e => {
      e.preventDefault();
      errorDiv.style.display = 'none';
      loader.style.display = 'block';

      fetch('register_ajax.php', {
        method: 'POST',
        body: new FormData(step1)
      })
      .then(res => res.json())
      .then(data => {
        loader.style.display = 'none';
        if (data.needOtp) {
          step1.style.display = 'none';
          step2.style.display = 'block';
        } else {
          showError(data.error || 'Unexpected error.');
        }
      })
      .catch(() => showError('Network error – please try again.'));
    });

    // STEP 2: Verify OTP & Register
    step2.addEventListener('submit', e => {
      e.preventDefault();
      errorDiv.style.display = 'none';
      loader.style.display = 'block';

      const fd = new FormData(step2);
      fd.append('plan', '<?= htmlspecialchars($plan) ?>');

      fetch('verify_otp.php', {
        method: 'POST',
        body: fd
      })
      .then(res => res.json())
      .then(data => {
        loader.style.display = 'none';
        if (data.success) {
          // Redirect to order or account
          if (data.plan) {
            window.location.href = `order?plan=${encodeURIComponent(data.plan)}`;
          } else {
            window.location.href = 'account';
          }
        } else {
          showError(data.error);
        }
      })
      .catch(() => showError('Verification failed – please try again.'));
    });
  </script>
</body>
</html>
