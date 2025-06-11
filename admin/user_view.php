<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo "<div class='bg-red-100 text-red-700 p-2 mb-4 rounded'>User not found.</div>";
    include 'footer.php';
    exit;
}

// Get user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<div class='bg-red-100 text-red-700 p-2 mb-4 rounded'>User not found.</div>";
    include 'footer.php';
    exit;
}

// Handle plan activation/deactivation
if (isset($_POST['toggle_plan'])) {
    $newStatus = $user['plan_activated'] ? 0 : 1;
    $stmt = $db->prepare("UPDATE users SET plan_activated=? WHERE id=?");
    $stmt->execute([$newStatus, $id]);
    header("Location: user_view.php?id=$id");
    exit;
}

// Handle quota update
if (isset($_POST['update_quota'])) {
    $newQuota = max(1, (int)$_POST['plan_quota']);
    $stmt = $db->prepare("UPDATE users SET plan_quota=? WHERE id=?");
    $stmt->execute([$newQuota, $id]);
    header("Location: user_view.php?id=$id");
    exit;
}

// User's API keys
$stmt = $db->prepare("SELECT * FROM user_api_keys WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user['user_id']]);
$api_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// User's message logs
$stmt = $db->prepare("SELECT * FROM user_message_logs WHERE user_id=? ORDER BY sent_at DESC LIMIT 10");
$stmt->execute([$id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold mb-2">User Details</h1>
    <div class="bg-white border rounded p-4 flex flex-col md:flex-row gap-10">
        <div>
            <div><strong>ID:</strong> <?= htmlspecialchars($user['id']) ?></div>
            <div><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
            <div><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?></div>
            <div><strong>WhatsApp:</strong> <?= htmlspecialchars($user['whatsapp']) ?></div>
            <div><strong>Plan Activated:</strong> <?= $user['plan_activated'] ? '<span class="text-green-600">Yes</span>' : '<span class="text-red-600">No</span>' ?></div>
            <div><strong>Plan Start:</strong> <?= $user['plan_start_date'] ?></div>
            <div><strong>Plan End:</strong> <?= $user['plan_end_date'] ?></div>
            <div><strong>Plan Quota:</strong> <?= $user['plan_quota'] ?></div>
            <div><strong>Messages Sent:</strong> <?= $user['messages_sent'] ?></div>
            <div><strong>API Calls:</strong> <?= $user['api_calls'] ?></div>
        </div>
        <div>
            <form method="post" class="mb-4">
                <button type="submit" name="toggle_plan"
                    class="bg-indigo-600 text-white px-4 py-1 rounded hover:bg-indigo-700">
                    <?= $user['plan_activated'] ? 'Deactivate Plan' : 'Activate Plan' ?>
                </button>
            </form>
            <form method="post" class="flex items-center gap-2 mb-2">
                <input type="number" name="plan_quota" value="<?= $user['plan_quota'] ?>" min="1" class="border px-2 py-1 rounded w-28" required />
                <button type="submit" name="update_quota" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Update Quota</button>
            </form>
        </div>
    </div>
</div>
<div class="mb-8">
    <h2 class="font-semibold text-lg mb-2">API Keys</h2>
    <?php if ($api_keys): ?>
        <ul class="list-disc ml-6">
            <?php foreach ($api_keys as $key): ?>
                <li>
                    <span class="font-mono"><?= htmlspecialchars($key['api_key']) ?></span>
                    <span class="text-gray-400 text-xs">[<?= $key['created_at'] ?>]</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <span class="text-gray-500">No API keys.</span>
    <?php endif; ?>
</div>
<div class="mb-8">
    <h2 class="font-semibold text-lg mb-2">Recent Message Logs</h2>
    <?php if ($logs): ?>
        <table class="w-full table-auto border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-1">Sent At</th>
                    <th class="px-2 py-1">Phone</th>
                    <th class="px-2 py-1">Status</th>
                    <th class="px-2 py-1">Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="border px-2 py-1"><?= $log['sent_at'] ?></td>
                        <td class="border px-2 py-1"><?= htmlspecialchars($log['phone_number']) ?></td>
                        <td class="border px-2 py-1"><?= $log['status'] == 'success' ? '<span class="text-green-600">Success</span>' : '<span class="text-red-600">Failure</span>' ?></td>
                        <td class="border px-2 py-1"><?= htmlspecialchars($log['message_text']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <span class="text-gray-500">No recent message logs.</span>
    <?php endif; ?>
</div>
<a href="users.php" class="text-indigo-700 underline">&larr; Back to Users</a>
<?php include 'footer.php'; ?>