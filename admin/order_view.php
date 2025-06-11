<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo "<div class='bg-red-100 text-red-700 p-2 mb-4 rounded'>Order not found.</div>";
    include 'footer.php';
    exit;
}

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='bg-red-100 text-red-700 p-2 mb-4 rounded'>Order not found.</div>";
    include 'footer.php';
    exit;
}

// Handle status updates (approve, reject, mark paid)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $stmt = $db->prepare("UPDATE orders SET status='approved' WHERE id=?");
        $stmt->execute([$id]);
    }
    if (isset($_POST['reject'])) {
        $stmt = $db->prepare("UPDATE orders SET status='rejected' WHERE id=?");
        $stmt->execute([$id]);
    }
    if (isset($_POST['mark_paid'])) {
        $stmt = $db->prepare("UPDATE orders SET payment_status='paid' WHERE id=?");
        $stmt->execute([$id]);
    }
    // Handle plan activation for user (now by user_id)
    if (isset($_POST['activate_plan']) && !empty($order['user_id'])) {
        // Find user by user_id (not email)
        $stmtUser = $db->prepare("SELECT * FROM users WHERE user_id=? LIMIT 1");
        $stmtUser->execute([$order['user_id']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            // Activate plan for 30 days, no plan_quota
            $now = date('Y-m-d H:i:s');
            $end = date('Y-m-d H:i:s', strtotime("+30 days"));
            $stmtAct = $db->prepare("UPDATE users SET plan_activated=1, plan_start_date=?, plan_end_date=? WHERE user_id=?");
            $stmtAct->execute([$now, $end, $user['user_id']]);
            $_SESSION['msg'] = "Plan activated for user!";
        } else {
            $_SESSION['msg'] = "No user found for this order user_id.";
        }
    }
    header("Location: order_view.php?id=$id");
    exit;
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold mb-2">Order Details</h1>
    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= htmlspecialchars($_SESSION['msg']) ?></div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>
    <div class="bg-white border rounded p-4 mb-4">
        <div><strong>Invoice ID:</strong> <?= htmlspecialchars($order['invoice_id']) ?></div>
        <div><strong>User ID:</strong> <?= htmlspecialchars($order['user_id']) ?></div>
        <div><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></div>
        <div><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></div>
        <div><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></div>
        <div><strong>Package:</strong> <?= htmlspecialchars($order['package']) ?></div>
        <div><strong>Price:</strong> <?= htmlspecialchars($order['price']) ?></div>
        <div><strong>Status:</strong> <?= ucfirst($order['status']) ?></div>
        <div><strong>Payment Status:</strong> <?= ucfirst($order['payment_status']) ?></div>
        <div><strong>Created At:</strong> <?= htmlspecialchars($order['created_at']) ?></div>
        <?php if ($order['screenshot']): ?>
            <div class="my-2">
                <strong>Screenshot:</strong><br>
                <img src="../screenshots/<?= htmlspecialchars($order['screenshot']) ?>" alt="Screenshot" class="border rounded w-60">
            </div>
        <?php endif; ?>
    </div>
    <form method="post" class="flex gap-2 mb-4">
        <?php if ($order['status'] !== 'approved'): ?>
            <button type="submit" name="approve" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Approve</button>
        <?php endif; ?>
        <?php if ($order['status'] !== 'rejected'): ?>
            <button type="submit" name="reject" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Reject</button>
        <?php endif; ?>
        <?php if ($order['payment_status'] !== 'paid'): ?>
            <button type="submit" name="mark_paid" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Mark as Paid</button>
        <?php endif; ?>
        <?php if ($order['status'] === 'approved' && !empty($order['user_id'])): ?>
            <button type="submit" name="activate_plan" class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">Activate Plan for User</button>
        <?php endif; ?>
    </form>
    <a href="orders.php" class="text-indigo-700 underline">&larr; Back to Orders</a>
</div>
<?php include 'footer.php'; ?>