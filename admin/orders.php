<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

// Filters
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$where = "1";
$params = [];

if ($status) {
    $where .= " AND status = :status";
    $params[':status'] = $status;
}
if ($search !== '') {
    $where .= " AND (invoice_id LIKE :search OR name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $params[':search'] = "%$search%";
}

$stmt = $db->prepare("SELECT * FROM orders WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-3">
    <h1 class="text-2xl font-bold">Orders</h1>
    <form method="get" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search order..." class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-300 flex-1 min-w-0"/>
        <select name="status" class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">All Status</option>
            <option value="pending" <?= $status == "pending" ? "selected" : "" ?>>Pending</option>
            <option value="approved" <?= $status == "approved" ? "selected" : "" ?>>Approved</option>
            <option value="rejected" <?= $status == "rejected" ? "selected" : "" ?>>Rejected</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition-all">Filter</button>
    </form>
</div>
<div class="overflow-x-auto rounded-lg border border-gray-200 shadow">
<table class="min-w-full text-sm text-left">
    <thead class="bg-gray-50 sticky top-0 z-10">
        <tr>
            <th class="px-3 py-2 font-semibold text-gray-700">#</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Invoice ID</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Name</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Email</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Phone</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Package</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Price</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Status</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Payment</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Created At</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
    <?php foreach ($orders as $o): ?>
        <tr class="hover:bg-indigo-50 transition">
            <td class="px-3 py-2"><?= htmlspecialchars($o['id']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($o['invoice_id']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($o['name']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($o['email']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($o['phone']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($o['package']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($o['price']) ?></td>
            <td class="px-3 py-2">
                <?php
                    $statusColor = 'bg-gray-200 text-gray-700';
                    if ($o['status'] == 'approved') $statusColor = 'bg-green-100 text-green-800';
                    elseif ($o['status'] == 'pending') $statusColor = 'bg-yellow-100 text-yellow-800';
                    elseif ($o['status'] == 'rejected') $statusColor = 'bg-red-100 text-red-800';
                ?>
                <span class="inline-block px-2 py-1 rounded <?= $statusColor ?> text-xs font-medium"><?= ucfirst($o['status']) ?></span>
            </td>
            <td class="px-3 py-2">
                <?php
                    $payColor = $o['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700';
                ?>
                <span class="inline-block px-2 py-1 rounded <?= $payColor ?> text-xs font-medium"><?= ucfirst($o['payment_status']) ?></span>
            </td>
            <td class="px-3 py-2"><?= htmlspecialchars($o['created_at']) ?></td>
            <td class="px-3 py-2">
                <a href="order_view.php?id=<?= $o['id'] ?>" class="text-indigo-600 hover:underline font-semibold">View</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
        <tr>
            <td class="px-3 py-4 text-center text-gray-500" colspan="11">No orders found.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php include 'footer.php'; ?>