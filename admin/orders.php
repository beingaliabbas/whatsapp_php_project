<?php
require_once '../db.php';
require_once 'includes/header.php';

// Update status if requested
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $newStatus = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : 'pending');
    $conn->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
    header("Location: orders.php");
    exit;
}

// Fetch orders
$stmt = $conn->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Page</title>
    <!-- Include Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Additional custom styles */
        .modal {
            display: none;
        }
        .modal.active {
            display: flex;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Orders Table Section -->
    <div class="p-6">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Orders</h2>

        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full table-auto text-sm text-gray-700">
                <thead class="bg-gray-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Invoice</th>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Phone</th>
                        <th class="px-6 py-3 text-left">Package</th>
                        <th class="px-6 py-3 text-left">Price</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Screenshot</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $i => $order): ?>
                    <tr class="border-b hover:bg-gray-100">
                        <td class="px-6 py-4"><?= $i + 1 ?></td>
                        <td class="px-6 py-4"><?= $order['invoice_id'] ?></td>
                        <td class="px-6 py-4"><?= $order['name'] ?></td>
                        <td class="px-6 py-4"><?= $order['email'] ?></td>
                        <td class="px-6 py-4"><?= $order['phone'] ?></td>
                        <td class="px-6 py-4"><?= $order['package'] ?></td>
                        <td class="px-6 py-4">Rs. <?= number_format($order['price']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-white <?= $order['status'] === 'approved' ? 'bg-green-500' : ($order['status'] === 'rejected' ? 'bg-red-500' : 'bg-yellow-500') ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($order['screenshot']): ?>
                                <button onclick="showScreenshot('<?= $order['screenshot'] ?>')" class="text-blue-600 hover:text-blue-800">View</button>
                            <?php else: ?>
                                <span class="text-gray-400">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <a href="?action=approve&id=<?= $order['id'] ?>" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-xs">Approve</a>
                            <a href="?action=reject&id=<?= $order['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-xs">Reject</a>
                            <a href="verify_order.php?id=<?= $order['id'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-xs">Verify Order</a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Screenshot Modal -->
    <div id="screenshotModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-3xl w-full relative">
            <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-600 hover:text-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h3 class="text-xl font-semibold mb-4 text-center">Payment Screenshot</h3>
            <img id="screenshotImage" src="" alt="Screenshot" class="w-full h-auto rounded-lg border">
        </div>
    </div>

    <script>
        function showScreenshot(filename) {
            const modal = document.getElementById('screenshotModal');
            const img = document.getElementById('screenshotImage');
            img.src = '../uploads/' + filename;
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('screenshotModal').classList.remove('active');
        }
    </script>

</body>
</html>
