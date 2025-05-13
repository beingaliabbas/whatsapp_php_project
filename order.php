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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <div class="col-lg-8 offset-lg-2">
    <div class="bg-white p-4 shadow rounded">
      <h3 class="mb-4">📋 Enter Your Details</h3>

      <form action="checkout_invoice.php" method="POST">
        <!-- Package Summary -->
        <div class="mb-4">
          <h5>📦 Selected Package</h5>
          <ul>
            <li><strong>Package:</strong> <?= $plan['name'] ?> (<?= $plan['accounts'] ?>)</li>
            <li><strong>Messages:</strong> <?= $plan['messages'] ?><?= $plan['media'] === 'Yes' ? ' + Media' : '' ?></li>
            <li><strong>Price:</strong> <span class="text-success">PKR <?= number_format($plan['price_pkr']) ?></span></li>
          </ul>
        </div>

        <!-- User Details -->
        <input type="hidden" name="plan_key" value="<?= $selected_plan_key ?>">
        <div class="mb-3">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email *</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Phone (Optional)</label>
          <input type="text" name="phone" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary w-100">Next: Checkout & Generate Invoice</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
