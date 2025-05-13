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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 mb-5">
  <div class="col-lg-8 offset-lg-2">
    <div class="bg-white p-4 shadow rounded">
      <h3 class="mb-4">🧾 Checkout & Upload Payment</h3>

      <!-- Invoice -->
      <div class="border rounded p-3 mb-4 bg-light">
        <p><strong>Invoice ID:</strong> <?= $invoice_id ?></p>
        <p><strong>Date:</strong> <?= $date ?></p>
        <p><strong>Status:</strong> <span class="badge bg-warning text-dark">Unpaid</span></p>
        <p><strong>Payment Method:</strong> Easypaisa - 03251387814 - <strong>Ali Abbas</strong></p>
      </div>

      <!-- Customer Info -->
      <h5>🙍 Customer</h5>
      <ul>
        <li><strong>Name:</strong> <?= htmlspecialchars($name) ?></li>
        <li><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
        <li><strong>Phone:</strong> <?= htmlspecialchars($phone ?: '-') ?></li>
      </ul>

      <!-- Package Info -->
      <h5 class="mt-3">📦 Package</h5>
      <ul>
        <li><strong>Package:</strong> <?= $plan['name'] ?> (<?= $plan['accounts'] ?>)</li>
        <li><strong>Messages:</strong> <?= $plan['messages'] ?><?= $plan['media'] === 'Yes' ? ' + Media' : '' ?></li>
        <li><strong>Amount:</strong> <span class="text-success fw-bold">PKR <?= number_format($plan['price_pkr']) ?></span></li>
      </ul>

      <!-- Payment Screenshot Upload -->
      <form action="process_order.php" method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
          <label class="form-label fw-bold">Pay & Submit Screenshot *</label>
          <input type="file" name="screenshot" class="form-control" required>
        </div>

        <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
        <input type="hidden" name="package" value="<?= $plan['name'] ?>">
        <input type="hidden" name="price" value="<?= $plan['price_pkr'] ?>">
        <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">

        <button type="submit" class="btn btn-success w-100">Submit & Activate</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
