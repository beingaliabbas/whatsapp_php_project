<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

// Fetch summary KPIs
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active_users = $db->query("SELECT COUNT(*) FROM users WHERE plan_activated=1")->fetchColumn();
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE payment_status='pending'")->fetchColumn();
$messages_sent = $db->query("SELECT COALESCE(SUM(messages_sent),0) FROM users")->fetchColumn();

// Total revenue (assumes 'price' column in PKR and paid orders)
$total_revenue = $db->query("SELECT COALESCE(SUM(price), 0) FROM orders WHERE payment_status='paid'")->fetchColumn();

// Last 30 days revenue (in PKR, paid orders)
$month_start = date("Y-m-01 00:00:00");
$month_end = date("Y-m-t 23:59:59");
$stmt = $db->prepare("SELECT COALESCE(SUM(price), 0) FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?");
$stmt->execute([$month_start, $month_end]);
$month_revenue = $stmt->fetchColumn();
?>
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600">Total Users</div>
        <div class="text-3xl font-bold"><?= $total_users ?></div>
    </div>
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600">Active Users</div>
        <div class="text-3xl font-bold"><?= $active_users ?></div>
    </div>
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600">Total Orders</div>
        <div class="text-3xl font-bold"><?= $total_orders ?></div>
    </div>
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600">Pending Orders</div>
        <div class="text-3xl font-bold"><?= $pending_orders ?></div>
    </div>
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600">Total Revenue</div>
        <div class="text-3xl font-bold"><?= number_format($total_revenue, 0) ?> PKR</div>
    </div>
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600">Revenue (<?= date("M Y") ?>)</div>
        <div class="text-3xl font-bold"><?= number_format($month_revenue, 0) ?> PKR</div>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600 mb-2">Total Messages Sent</div>
        <div class="text-2xl font-bold"><?= $messages_sent ?></div>
    </div>
    <div class="bg-white border rounded p-4 text-center shadow">
        <div class="text-gray-600 mb-2">Revenue Trend (<?= date("M Y") ?>)</div>
        <div class="w-full overflow-x-auto">
            <?php
            // Prepare daily revenue for the current month
            $days_in_month = date("t");
            $revenues = [];
            for ($d = 1; $d <= $days_in_month; $d++) {
                $day = sprintf("%02d", $d);
                $day_start = date("Y-m-") . $day . " 00:00:00";
                $day_end = date("Y-m-") . $day . " 23:59:59";
                $stmt = $db->prepare("SELECT COALESCE(SUM(price),0) FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?");
                $stmt->execute([$day_start, $day_end]);
                $revenues[] = (float)$stmt->fetchColumn();
            }
            ?>
            <div class="flex items-end gap-1 h-24 mt-4">
                <?php
                $max_rev = max($revenues) ?: 1;
                foreach ($revenues as $i => $rev) {
                    $bar_height = $max_rev > 0 ? intval(($rev / $max_rev) * 90) : 0;
                    echo "<div class='flex flex-col items-center'>
                            <div class='bg-indigo-500 rounded w-3' style='height:{$bar_height}px' title='Day " . ($i+1) . ": " . number_format($rev, 0) . " PKR'></div>
                            <div class='text-xs text-gray-400 mt-1'>" . ($i+1) . "</div>
                        </div>";
                }
                ?>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-2">
                <span>1</span>
                <span><?= $days_in_month ?></span>
            </div>
            <div class="text-xs text-gray-500 mt-2">Each bar = revenue for that day</div>
        </div>
    </div>
</div>
<!-- Recent Orders Table -->
<h2 class="font-semibold text-lg mb-2">Recent Orders</h2>
<div class="overflow-x-auto rounded-lg border border-gray-200 shadow mb-8">
<table class="min-w-full text-sm text-left">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-3 py-2 font-semibold text-gray-700">Invoice ID</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Name</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Package</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Status</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Created</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
    <?php
    $orders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($orders as $order) {
        // Status badge color
        $status = ucfirst($order['status']);
        $status_color = 'bg-gray-200 text-gray-700';
        if ($order['status'] === 'approved') $status_color = 'bg-green-100 text-green-800';
        elseif ($order['status'] === 'pending') $status_color = 'bg-yellow-100 text-yellow-800';
        elseif ($order['status'] === 'rejected') $status_color = 'bg-red-100 text-red-800';

        echo "<tr class='hover:bg-indigo-50 transition'>
            <td class='px-3 py-2'>" . htmlspecialchars($order['invoice_id']) . "</td>
            <td class='px-3 py-2'>" . htmlspecialchars($order['name']) . "</td>
            <td class='px-3 py-2'>" . htmlspecialchars($order['package']) . "</td>
            <td class='px-3 py-2'><span class='inline-block px-2 py-1 rounded $status_color text-xs font-medium'>$status</span></td>
            <td class='px-3 py-2'>" . htmlspecialchars($order['created_at']) . "</td>
            <td class='px-3 py-2'>
                <a href='order_view.php?id=" . $order['id'] . "' class='text-indigo-600 hover:underline font-semibold'>View</a>
            </td>
        </tr>";
    }
    if (empty($orders)) {
        echo "<tr><td colspan='6' class='px-3 py-4 text-center text-gray-500'>No recent orders.</td></tr>";
    }
    ?>
    </tbody>
</table>
</div>
<?php include 'footer.php'; ?>