<?php
session_start();
require_once "db.php";

// Simple admin login check (replace with your admin check)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$errors = [];
$success = "";

// Handle add/edit payment method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method_name = trim($_POST['method_name'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $edit_id = intval($_POST['edit_id'] ?? 0);

    if (!$method_name) $errors[] = "Method Name is required.";
    if (!$details) $errors[] = "Details are required.";

    if (!$errors) {
        if ($edit_id) {
            // Update
            $stmt = $db->prepare("UPDATE payment_settings SET method_name=?, details=?, enabled=?, sort_order=? WHERE id=?");
            $stmt->execute([$method_name, $details, $enabled, $sort_order, $edit_id]);
            $success = "Payment method updated successfully.";
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO payment_settings (method_name, details, enabled, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$method_name, $details, $enabled, $sort_order]);
            $success = "New payment method added.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM payment_settings WHERE id=?");
    $stmt->execute([$id]);
    $success = "Payment method deleted.";
}

// Handle edit display
$edit_method = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM payment_settings WHERE id=?");
    $stmt->execute([$id]);
    $edit_method = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all payment methods
$stmt = $db->query("SELECT * FROM payment_settings ORDER BY sort_order ASC, id ASC");
$methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Payment Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-6 text-indigo-700">Payment Methods Settings</h1>

        <?php if ($errors): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow rounded p-5 mb-7">
            <form method="post">
                <input type="hidden" name="edit_id" value="<?= $edit_method['id'] ?? 0 ?>">
                <div class="mb-3">
                    <label class="block font-medium mb-1">Method Name</label>
                    <input type="text" name="method_name" required class="w-full border px-3 py-2 rounded"
                        value="<?= htmlspecialchars($edit_method['method_name'] ?? '') ?>" maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="block font-medium mb-1">Details<br>
                        <span class="text-xs text-gray-500">e.g. Account #, IBAN, Wallet, Owner</span>
                    </label>
                    <textarea name="details" required class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($edit_method['details'] ?? '') ?></textarea>
                </div>
                <div class="mb-3 flex items-center space-x-4">
                    <label>
                        <input type="checkbox" name="enabled" value="1" <?= (isset($edit_method) && $edit_method['enabled']) || !isset($edit_method) ? 'checked' : '' ?>>
                        <span class="ml-1">Enabled</span>
                    </label>
                    <label>
                        <span class="mr-1">Sort Order</span>
                        <input type="number" name="sort_order" value="<?= intval($edit_method['sort_order'] ?? 0) ?>" class="w-16 border rounded px-2 py-1">
                    </label>
                </div>
                <div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-semibold">
                        <?= $edit_method ? "Update" : "Add" ?> Payment Method
                    </button>
                    <?php if ($edit_method): ?>
                        <a href="admin_payment_settings.php" class="ml-4 text-sm text-gray-600 hover:underline">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h2 class="text-xl font-semibold mb-3">All Payment Methods</h2>
        <?php if (!$methods): ?>
            <div class="bg-yellow-100 text-yellow-900 p-3 rounded">No payment methods set yet.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded shadow">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-3 py-2 text-left">#</th>
                            <th class="px-3 py-2 text-left">Method</th>
                            <th class="px-3 py-2 text-left">Details</th>
                            <th class="px-3 py-2 text-left">Enabled</th>
                            <th class="px-3 py-2 text-left">Sort</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($methods as $i => $m): ?>
                        <tr>
                            <td class="border-t px-3 py-2"><?= $i+1 ?></td>
                            <td class="border-t px-3 py-2"><?= htmlspecialchars($m['method_name']) ?></td>
                            <td class="border-t px-3 py-2 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($m['details'])) ?></td>
                            <td class="border-t px-3 py-2"><?= $m['enabled'] ? "Yes" : "No" ?></td>
                            <td class="border-t px-3 py-2"><?= intval($m['sort_order']) ?></td>
                            <td class="border-t px-3 py-2 text-right">
                                <a href="?edit=<?= $m['id'] ?>" class="text-indigo-600 hover:underline mr-2">Edit</a>
                                <a href="?delete=<?= $m['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this payment method?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>