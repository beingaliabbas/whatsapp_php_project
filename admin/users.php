<?php
require_once '../db.php';

// Handle form submission for updating plan status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    $planActivated = isset($_POST['plan_activated']) ? 1 : 0;
    $planDays = intval($_POST['plan_days']);

    if ($planActivated && $planDays > 0) {
        $planStart = date('Y-m-d H:i:s');
        $planEnd = date('Y-m-d H:i:s', strtotime("+$planDays days"));
    } else {
        $planStart = null;
        $planEnd = null;
    }

    $updateStmt = $conn->prepare("UPDATE users SET plan_activated = ?, plan_start_date = ?, plan_end_date = ? WHERE id = ?");
    $updateStmt->execute([$planActivated, $planStart, $planEnd, $userId]);

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch users
$stmt = $conn->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Admin panel user listing and plan activation management" />
  <meta name="author" content="Ali Abbas" />
  <title>Users | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
  <div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Users List &amp; Plan Management</h1>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <!-- Add responsive horizontal scroll wrapper -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-auto">
          <thead class="bg-gray-800 text-white whitespace-nowrap">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">#</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Username</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">API Key</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">User ID</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Plan Active</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Plan Start</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Plan End</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Set Plan Period (days)</th>
              <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 whitespace-nowrap">
            <?php foreach ($users as $i => $user): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-700"><?= $i + 1 ?></td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($user['email']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700">
                <?= $user['api_key'] ? htmlspecialchars($user['api_key']) : '<span class="text-red-500">N/A</span>' ?>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($user['user_id']) ?></td>
              <td class="px-6 py-4 text-sm">
                <?= $user['plan_activated'] ?
                  '<span class="text-green-600 font-semibold">Active</span>' :
                  '<span class="text-red-600 font-semibold">Inactive</span>' ?>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                <?= $user['plan_start_date'] ? date('Y-m-d', strtotime($user['plan_start_date'])) : '-' ?>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                <?= $user['plan_end_date'] ? date('Y-m-d', strtotime($user['plan_end_date'])) : '-' ?>
              </td>
              <td class="px-6 py-4">
                <form method="post" class="flex items-center space-x-2 whitespace-normal">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>" />
                  <label class="inline-flex items-center space-x-2">
                    <input
                      type="checkbox"
                      name="plan_activated"
                      value="1"
                      <?= $user['plan_activated'] ? 'checked' : '' ?>
                      class="form-checkbox h-5 w-5 text-blue-600"
                    />
                    <span class="text-gray-700 text-sm">Active</span>
                  </label>
                  <input
                    type="number"
                    name="plan_days"
                    min="1"
                    placeholder="Days"
                    class="border rounded px-2 py-1 text-sm w-20"
                    required="<?= $user['plan_activated'] ? 'required' : '' ?>"
                    value="<?= $user['plan_activated'] && $user['plan_end_date'] && $user['plan_start_date']
                      ? ( (strtotime($user['plan_end_date']) - strtotime($user['plan_start_date'])) / 86400 )
                      : '' ?>"
                  />
                  <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded transition"
                  >
                    Update
                  </button>
                </form>
              </td>
              <td class="px-6 py-4">
                <a href="/send-message/<?= urlencode($user['user_id']) ?>" class="inline-block px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700 transition">Send Message</a>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
