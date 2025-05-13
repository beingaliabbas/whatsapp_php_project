    <?php
require_once '../db.php';
require_once 'includes/header.php';

// Fetch users
$stmt = $conn->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Users</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full table-auto bg-white rounded shadow">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Username</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">API Key</th>
                    <th class="px-4 py-2 text-left">User ID</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $user): ?>
                    <tr class="border-b hover:bg-gray-100">
                        <td class="px-4 py-2"><?= $i + 1 ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-4 py-2"><?= $user['api_key'] ?: '<span class="text-red-500">N/A</span>' ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($user['user_id']) ?></td>
                        <td class="px-4 py-2">
                            <a href="#" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
