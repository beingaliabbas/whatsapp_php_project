<?php
include("db.php");

$plans = [
  'basic' => ['name' => 'Basic', 'accounts' => '1 WhatsApp Account', 'media' => 'No', 'messages' => 'Unlimited Messages', 'price_pkr' => 999],
  'pro' => ['name' => 'Pro', 'accounts' => '2 WhatsApp Accounts', 'media' => 'Yes', 'messages' => 'Unlimited Messages', 'price_pkr' => 2000],
  'enterprise' => ['name' => 'Enterprise', 'accounts' => '3 WhatsApp Accounts', 'media' => 'Yes', 'messages' => 'Unlimited Messages', 'price_pkr' => 3000]
];

$selected_plan_key = $_GET['plan'] ?? 'basic';
$plan = $plans[$selected_plan_key] ?? $plans['basic'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Package - WhatsApp API</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

<?php include("header.php");?> 

<div class="max-w-3xl mx-auto mt-10 px-4">
  <div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-indigo-600">📋 Enter Your Details</h2>

    <!-- Package Summary -->
    <div class="bg-indigo-50 p-4 rounded-md mb-6">
      <h3 class="text-lg font-semibold text-indigo-700 mb-2">📦 Selected Package</h3>
      <ul class="text-sm text-gray-700 space-y-1">
        <li><strong>Package:</strong> <?= $plan['name'] ?> (<?= $plan['accounts'] ?>)</li>
        <li><strong>Messages:</strong> <?= $plan['messages'] ?><?= $plan['media'] === 'Yes' ? ' + Media' : '' ?></li>
        <li><strong>Price:</strong> <span class="text-green-600 font-medium">PKR <?= number_format($plan['price_pkr']) ?></span></li>
      </ul>
    </div>

    <!-- User Details Form -->
    <form action="checkout_invoice" method="POST" class="space-y-5">
      <input type="hidden" name="plan_key" value="<?= $selected_plan_key ?>">

      <div>
        <label class="block text-sm font-medium mb-1">Full Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
        <input type="email" name="email" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Phone (Optional)</label>
        <input type="text" name="phone" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md text-lg font-medium transition duration-200">
        Next: Checkout & Generate Invoice
      </button>
    </form>
  </div>
</div>

</body>
</html>
