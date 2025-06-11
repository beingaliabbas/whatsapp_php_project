<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

// Optional: Filter by status
$status = $_GET['status'] ?? '';
$where = "1";
$params = [];
if ($status) {
    $where .= " AND status = :status";
    $params[':status'] = $status;
}
$stmt = $db->prepare("SELECT * FROM sessions WHERE $where ORDER BY last_connected_at DESC");
$stmt->execute($params);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold mb-4">WhatsApp Sessions</h1>
    <form method="get" class="flex gap-2 mb-4">
        <select name="status" class="border px-2 py-1 rounded">
            <option value="">All Status</option>
            <option value="connected" <?= $status == "connected" ? "selected" : "" ?>>Connected</option>
            <option value="disconnected" <?= $status == "disconnected" ? "selected" : "" ?>>Disconnected</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full table-auto border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-1">ID</th>
                    <th class="px-2 py-1">User ID</th>
                    <th class="px-2 py-1">Client ID</th>
                    <th class="px-2 py-1">Phone Number</th>
                    <th class="px-2 py-1">Status</th>
                    <th class="px-2 py-1">Last Connected</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sessions as $s): ?>
                <tr>
                    <td class="border px-2 py-1"><?= $s['id'] ?></td>
                    <td class="border px-2 py-1"><?= $s['user_id'] ?></td>
                    <td class="border px-2 py-1"><?= htmlspecialchars($s['client_id']) ?></td>
                    <td class="border px-2 py-1"><?= htmlspecialchars($s['phone_number']) ?></td>
                    <td class="border px-2 py-1"><?= $s['status'] === 'connected' ? '<span class="text-green-600">Connected</span>' : '<span class="text-red-600">Disconnected</span>' ?></td>
                    <td class="border px-2 py-1"><?= $s['last_connected_at'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>