<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

// Handle custom days form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Custom days form
    if (isset($_POST['set_custom_days'], $_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $days = max(1, min(3650, (int)$_POST['custom_days'])); // clamp between 1 and 3650
        $now = date('Y-m-d H:i:s');
        $end = date('Y-m-d H:i:s', strtotime("+$days days"));
        $stmt = $db->prepare("UPDATE users SET plan_activated=1, plan_start_date=?, plan_end_date=? WHERE id=?");
        $stmt->execute([$now, $end, $user_id]);
        echo "<div class='bg-green-100 text-green-700 p-2 mb-4 rounded shadow'>Custom plan set for $days days for user #$user_id.</div>";
    }
    // Plan activate/deactivate form
    if (isset($_POST['toggle_plan'], $_POST['user_id'], $_POST['plan_status'])) {
        $user_id = (int)$_POST['user_id'];
        $plan_status = $_POST['plan_status'] == "1" ? 1 : 0;
        $stmt = $db->prepare("UPDATE users SET plan_activated=? WHERE id=?");
        $stmt->execute([$plan_status, $user_id]);
        $msg = $plan_status ? "Plan activated" : "Plan deactivated";
        echo "<div class='bg-green-100 text-green-700 p-2 mb-4 rounded shadow'>$msg for user #$user_id.</div>";
    }
}

// Filters/search
$search = trim($_GET['search'] ?? '');
$where = "1";
$params = [];
if ($search !== '') {
    $where .= " AND (username LIKE :search OR email LIKE :search OR fullname LIKE :search OR whatsapp LIKE :search)";
    $params[':search'] = "%$search%";
}

// Get users
$stmt = $db->prepare("SELECT * FROM users WHERE $where ORDER BY id DESC");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to calculate selected days
function get_plan_days($start, $end) {
    if (!$start || !$end) return '';
    $start_dt = new DateTime($start);
    $end_dt = new DateTime($end);
    $diff = $start_dt->diff($end_dt);
    return $diff->days;
}
?>
<div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-3">
    <h1 class="text-2xl font-bold">Users</h1>
    <form method="get" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search user..." class="border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-300 flex-1 min-w-0"/>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition-all">Search</button>
    </form>
</div>
<div class="overflow-x-auto rounded-lg border border-gray-200 shadow">
<table class="min-w-full text-sm text-left">
    <thead class="bg-gray-50 sticky top-0 z-10">
        <tr>
            <th class="px-3 py-2 font-semibold text-gray-700">#</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Username</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Email</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Full Name</th>
            <th class="px-3 py-2 font-semibold text-gray-700">WhatsApp</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Plan</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Active</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Messages Sent</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Plan End</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Selected Days</th>
            <th class="px-3 py-2 font-semibold text-gray-700">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
    <?php foreach ($users as $u): ?>
        <tr class="hover:bg-indigo-50 transition">
            <td class="px-3 py-2"><?= htmlspecialchars($u['id']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($u['username']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($u['email']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($u['fullname']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($u['whatsapp']) ?></td>
            <td class="px-3 py-2">
                <?php
                    $planColor = $u['plan_quota'] == 1000 ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
                    $planName = $u['plan_quota'] == 1000 ? 'Basic' : 'Custom';
                ?>
                <span class="inline-block px-2 py-1 rounded <?= $planColor ?> text-xs font-medium"><?= $planName ?></span>
            </td>
            <td class="px-3 py-2">
                <form method="post" action="" class="inline">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="plan_status" class="border rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="1" <?= $u['plan_activated'] ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= !$u['plan_activated'] ? 'selected' : '' ?>>No</option>
                    </select>
                    <button type="submit" name="toggle_plan" class="bg-gray-200 text-xs px-2 py-0.5 rounded hover:bg-indigo-100 transition">Save</button>
                </form>
            </td>
            <td class="px-3 py-2"><?= htmlspecialchars($u['messages_sent']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($u['plan_end_date']) ?></td>
            <td class="px-3 py-2">
                <?php
                if ($u['plan_activated'] && $u['plan_start_date'] && $u['plan_end_date']) {
                    echo get_plan_days($u['plan_start_date'], $u['plan_end_date']) . ' days';
                } else {
                    echo '-';
                }
                ?>
            </td>
            <td class="px-3 py-2">
                <a href="user_view.php?id=<?= $u['id'] ?>" class="text-indigo-600 hover:underline font-semibold">View</a>
                <form method="post" action="" class="inline ml-2">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="number" name="custom_days" min="1" max="3650"
                        value="<?= $u['plan_activated'] && $u['plan_start_date'] && $u['plan_end_date'] ? get_plan_days($u['plan_start_date'], $u['plan_end_date']) : 30 ?>"
                        class="border rounded px-1 py-0.5 text-xs w-16 focus:outline-none focus:ring-2 focus:ring-indigo-200"/>
                    <button type="submit" name="set_custom_days" class="bg-gray-200 text-xs px-2 py-0.5 rounded hover:bg-indigo-100 transition">Set Days</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($users)): ?>
        <tr>
            <td class="px-3 py-4 text-center text-gray-500" colspan="11">No users found.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php include 'footer.php'; ?>