<?php
include("db.php");

$plans = [
  'basic' => ['name' => 'Basic', 'accounts' => '1 WhatsApp Account', 'media' => 'No', 'messages' => 'Unlimited Messages', 'price_pkr' => 999],
  'pro' => ['name' => 'Pro', 'accounts' => '2 WhatsApp Accounts', 'media' => 'Yes', 'messages' => 'Unlimited Messages', 'price_pkr' => 2000],
  'enterprise' => ['name' => 'Enterprise', 'accounts' => '3 WhatsApp Accounts', 'media' => 'Yes', 'messages' => 'Unlimited Messages', 'price_pkr' => 3000]
];

$plan_key = $_POST['plan_key'] ?? 'basic';
$plan = $plans[$plan_key] ?? $plans['basic'];

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$invoice_id = "INV-" . date("YmdHis");
$date = date("d M, Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout Invoice - WhatsApp API</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

<?php include("header.php"); ?> 

<div class="max-w-3xl mx-auto mt-10 px-4">
  <div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-indigo-600">🧾 Checkout & Upload Payment</h2>

    <!-- Invoice Info -->
    <div class="bg-yellow-50 p-4 rounded-md mb-6 border border-yellow-200">
      <p><strong>Invoice ID:</strong> <?= $invoice_id ?></p>
      <p><strong>Date:</strong> <?= $date ?></p>
      <p><strong>Status:</strong> <span class="inline-block bg-yellow-300 text-yellow-900 text-xs px-2 py-1 rounded">Unpaid</span></p>
      <p><strong>Payment Method:</strong> Easypaisa - <span class="text-gray-800 font-medium">03251387814 - Ali Abbas</span></p>
    </div>

    <!-- Customer Info -->
    <div class="mb-5">
      <h3 class="text-lg font-semibold mb-2 text-gray-700">🙍 Customer</h3>
      <ul class="text-sm text-gray-700 space-y-1">
        <li><strong>Name:</strong> <?= htmlspecialchars($name) ?></li>
        <li><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
        <li><strong>Phone:</strong> <?= htmlspecialchars($phone ?: '-') ?></li>
      </ul>
    </div>

    <!-- Package Info -->
    <div class="mb-6">
      <h3 class="text-lg font-semibold mb-2 text-gray-700">📦 Package</h3>
      <ul class="text-sm text-gray-700 space-y-1">
        <li><strong>Package:</strong> <?= $plan['name'] ?> (<?= $plan['accounts'] ?>)</li>
        <li><strong>Messages:</strong> <?= $plan['messages'] ?><?= $plan['media'] === 'Yes' ? ' + Media' : '' ?></li>
        <li><strong>Amount:</strong> <span class="text-green-600 font-medium">PKR <?= number_format($plan['price_pkr']) ?></span></li>
      </ul>
    </div>

    <!-- Payment Screenshot Upload -->
    <form action="process_order" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Pay & Upload Screenshot <span class="text-red-500">*</span></label>
        <input type="file" name="screenshot" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
      </div>

      <!-- Hidden Inputs -->
      <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
      <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
      <input type="hidden" name="package" value="<?= $plan['name'] ?>">
      <input type="hidden" name="price" value="<?= $plan['price_pkr'] ?>">
      <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">

      <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md text-lg font-medium transition duration-200">
        Submit & Activate
      </button>
    </form>
  </div>
</div>

</body>
</html>
