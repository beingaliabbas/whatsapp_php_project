<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

// Optional: Filter by user or status
$user_id = (int)($_GET['user_id'] ?? 0);
$status = $_GET['status'] ?? '';

$where = "1";
$params = [];
if ($user_id) {
    $where .= " AND user_id = :user_id";
    $params[':user_id'] = $user_id;
}
if ($status) {
    $where .= " AND status = :status";
    $params[':status'] = $status;
}

// Fetch logs directly from user_message_logs with user_email
$stmt = $db->prepare(
    "SELECT * FROM user_message_logs
     WHERE $where
     ORDER BY user_email ASC, sent_at DESC
     LIMIT 100"
);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold mb-4">Message Logs</h1>
    <form method="get" class="flex gap-2 mb-4">
        <input type="text" name="user_id" placeholder="User ID" value="<?= $user_id ?: '' ?>" class="border px-2 py-1 rounded"/>
        <select name="status" class="border px-2 py-1 rounded">
            <option value="">All Status</option>
            <option value="success" <?= $status == "success" ? "selected" : "" ?>>Success</option>
            <option value="failure" <?= $status == "failure" ? "selected" : "" ?>>Failure</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full table-auto border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-1">ID</th>
                    <th class="px-2 py-1">User Email</th>
                    <th class="px-2 py-1">Phone</th>
                    <th class="px-2 py-1">Sent At</th>
                    <th class="px-2 py-1">Status</th>
                    <th class="px-2 py-1">Message</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="border px-2 py-1"><?= $log['id'] ?></td>
                    <td class="border px-2 py-1"><?= htmlspecialchars($log['user_email']) ?></td>
                    <td class="border px-2 py-1"><?= htmlspecialchars($log['phone_number']) ?></td>
                    <td class="border px-2 py-1"><?= $log['sent_at'] ?></td>
                    <td class="border px-2 py-1"><?= $log['status'] === 'success' ? '<span class="text-green-600">Success</span>' : '<span class="text-red-600">Failure</span>' ?></td>
                    <td class="border px-2 py-1"><?= htmlspecialchars($log['message_text']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>