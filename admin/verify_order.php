<?php
require_once '../db.php';
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600'>Invalid Order ID.</p>";
    exit;
}

$orderId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p class='text-red-600'>Order not found.</p>";
    exit;
}

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    // Activate user plan
    $days = intval($order['days']);
    $expireAt = date('Y-m-d H:i:s', strtotime("+$days days"));
    
    $updateUser = $conn->prepare("UPDATE users SET subscription = 1, plan = ?, expire_at = ? WHERE id = ?");
    $updateUser->execute([$order['package'], $expireAt, $order['user_id']]);

    $updateOrder = $conn->prepare("UPDATE orders SET status = 'approved' WHERE id = ?");
    $updateOrder->execute([$orderId]);

    echo "<div class='bg-green-100 text-green-800 px-4 py-2 rounded mb-4'>✅ Subscription activated for user ID {$order['user_id']}</div>";
    $order['status'] = 'approved'; // update status in the UI
}
?>

<div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Verify Payment & Activate Plan</h2>

    <div class="bg-white shadow p-4 rounded">
        <p><strong>Invoice:</strong> <?= $order['invoice_id'] ?></p>
        <p><strong>Name:</strong> <?= $order['name'] ?></p>
        <p><strong>Email:</strong> <?= $order['email'] ?></p>
        <p><strong>Phone:</strong> <?= $order['phone'] ?></p>
        <p><strong>Package:</strong> <?= $order['package'] ?></p>
        <p><strong>Days:</strong> <?= $order['days'] ?></p>
        <p><strong>Price:</strong> Rs. <?= number_format($order['price']) ?></p>
        <p><strong>Status:</strong>
            <span class="font-semibold <?= $order['status'] === 'approved' ? 'text-green-600' : 'text-yellow-600' ?>">
                <?= ucfirst($order['status']) ?>
            </span>
        </p>

        <?php if ($order['screenshot']): ?>
            <div class="mt-4">
                <p class="mb-2 font-semibold">Payment Screenshot:</p>
                <img src="../uploads/<?= $order['screenshot'] ?>" alt="Payment Screenshot" class="w-full max-w-md rounded border">
            </div>
        <?php endif; ?>

        <?php if ($order['status'] !== 'approved'): ?>
        <form method="POST" class="mt-6">
            <button name="verify" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                ✅ Verify & Activate Plan
            </button>
        </form>
        <?php else: ?>
            <p class="mt-4 text-green-600 font-semibold">✅ This order is already verified.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
