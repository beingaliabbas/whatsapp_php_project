<?php include("head.php"); ?>
<body class="bg-gray-100 text-gray-900 min-h-screen">
<?php include("header.php"); ?>
<?php include("db.php"); ?>
<?php include("whatsapp_send.php"); ?>

<main class="max-w-3xl mx-auto mt-12 px-4 py-8 bg-white rounded-xl shadow-lg">
  <h1 class="text-3xl font-bold text-indigo-700 mb-4">Contact Us</h1>
  <p class="text-lg mb-6">Have questions or need support? Fill out the form below and our team will get back to you soon.</p>

  <?php
  $success = '';
  $errMsg = '';

  // Get admin WhatsApp number from admin_users table
  function get_admin_whatsapp($conn) {
      $stmt = $conn->query("SELECT whatsapp FROM admin_users WHERE whatsapp IS NOT NULL AND whatsapp <> '' LIMIT 1");
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      // Ensure it's in 923XXXXXXXXX format
      if ($row && isset($row['whatsapp'])) {
          $phone = preg_replace('/[^0-9]/', '', $row['whatsapp']);
          if (substr($phone, 0, 2) == '03' && strlen($phone) == 11) {
              $phone = '92' . substr($phone, 1);
          }
          elseif (substr($phone, 0, 3) == '923' && strlen($phone) == 12) {
              // already correct
          } else {
              $phone = ''; // Not valid
          }
          return $phone;
      }
      return '';
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $message = trim($_POST['message'] ?? '');

      if (!$name || !$email || !$message) {
          $errMsg = "Please fill in all fields.";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $errMsg = "Please enter a valid email address.";
      } else {
          // Send WhatsApp to Admin
          $admin_phone = get_admin_whatsapp($conn);
          if (!$admin_phone) {
              $errMsg = "Admin WhatsApp number not found. Please try again later.";
          } else {
              $waMessage =
                  "*Contact Form Submission*\n"
                  . "---------------------------------\n"
                  . "*Name:* $name\n"
                  . "*Email:* $email\n"
                  . "*Message:*\n$message\n"
                  . "---------------------------------\n"
                  . "Sent from APIWhale Contact Page";

              $waSend = send_whatsapp_message($admin_phone, $waMessage);

              if (!$waSend['success']) {
                  $errMsg = "Could not send message to admin: " . ($waSend['error'] ?? 'Unknown error');
              } else {
                  $success = "Thank you, $name! We have received your message and will contact you soon.";
                  // Clear values
                  $name = $email = $message = '';
                  // Optionally, you may also send an email or store message in DB here
              }
          }
      }
  }
  ?>
  <?php if ($success): ?>
    <div class="bg-green-100 border border-green-300 text-green-700 rounded-md px-4 py-3 mb-6">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>
  <?php if ($errMsg): ?>
    <div class="bg-red-100 border border-red-300 text-red-700 rounded-md px-4 py-3 mb-6">
      <?= htmlspecialchars($errMsg) ?>
    </div>
  <?php endif; ?>

  <form action="" method="POST" class="space-y-5">
    <div>
      <label class="block text-sm font-medium mb-1">Your Name <span class="text-red-500">*</span></label>
      <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required maxlength="64"
             class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Your Email <span class="text-red-500">*</span></label>
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required maxlength="64"
             class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Message <span class="text-red-500">*</span></label>
      <textarea name="message" required rows="5" maxlength="2000"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
    </div>
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-lg font-semibold transition duration-200">
      Send Message
    </button>
  </form>

  <div class="mt-10 text-sm text-gray-600">
    <div class="mb-1"><i class="fas fa-map-marker-alt text-indigo-600"></i> Office #123, Tech Avenue, Karachi, Pakistan</div>
    <div class="mb-1"><i class="fas fa-envelope text-indigo-600"></i> support@waapiservices.com</div>
    <div><i class="fab fa-whatsapp text-green-600"></i> +92 321 5387814</div>
  </div>
</main>
</body>