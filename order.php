<?php
session_start();
include("db.php");
$user_id = $_SESSION['user_id'] ?? null;

// Add mpdf and WhatsApp sender
require_once __DIR__ . '/vendor/autoload.php'; // mPDF (composer install)
include 'whatsapp_send.php';

// Helper to escape output
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Single plan only
$plan = [
    'name' => 'WhatsApp Messaging',
    'accounts' => '1 WhatsApp Account',
    'media' => 'No',
    'messages' => 'Unlimited Messages',
    'price_pkr' => 999
];

// Step control
$step = 1;
$errors = [];
$orderCompleted = false;

// Default fields
$name = $_POST['name'] ?? ($_SESSION['user_name'] ?? '');
$email = $_POST['email'] ?? ($_SESSION['user_email'] ?? '');
$phone = $_POST['phone'] ?? ($_SESSION['user_phone'] ?? '');

// Invoice vars for PDF/WhatsApp (will be set in step 2)
$invoice_id = "";
$date = "";
$pdfPath = "";
$pdfUrl = "";

// Helper to get admin WhatsApp number
function get_admin_whatsapp($conn) {
    $stmt = $conn->query("SELECT whatsapp FROM admin_users WHERE whatsapp IS NOT NULL AND whatsapp <> '' LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['whatsapp'])) {
        $phone = preg_replace('/[^0-9]/', '', $row['whatsapp']);
        if (substr($phone, 0, 2) == '03' && strlen($phone) == 11) {
            $phone = '92' . substr($phone, 1);
        } elseif (substr($phone, 0, 3) == '923' && strlen($phone) == 12) {
            // already correct
        } else {
            $phone = '';
        }
        return $phone;
    }
    return '';
}

// Fetch payment methods from payment_settings table
$payment_methods = [];
try {
    $stmt = $conn->query("SELECT * FROM payment_settings WHERE enabled = 1 ORDER BY sort_order ASC, id ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $payment_methods[] = $row;
    }
} catch (Exception $e) {
    $errors[] = "Failed to load payment methods: " . $e->getMessage();
}

// Step 2: Invoice details
if (isset($_POST['proceed_to_invoice'])) {
    // Validate fields
    if (!$name) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";

    // Phone validation: must be 923XXXXXXXXX (no +, no spaces, 12 digits, starts with 92 or 03)
    $phone = preg_replace('/[^0-9]/', '', $phone); // Remove everything except digits
    if (substr($phone, 0, 2) == '03' && strlen($phone) == 11) {
        $phone = '92' . substr($phone, 1);  // Convert 03xxxxxxxxx to 923xxxxxxxxx
    } elseif (substr($phone, 0, 3) == '923' && strlen($phone) == 12) {
        // already correct
    } else {
        $errors[] = "Phone must be Pakistani 923XXXXXXXXX format, no + or spaces.";
    }

    if (!$errors) {
        $step = 2;

        // --- Generate PDF Invoice ---
        $invoice_id = "INV-" . date("YmdHis");
        $date = date("d M, Y");

        // Make invoices directory if not exists
        $invoiceDir = __DIR__ . '/invoices/';
        if (!is_dir($invoiceDir)) mkdir($invoiceDir, 0755, true);

        // File name as invoice number
        $invoiceFileName = $invoice_id . ".pdf";
        $baseUrl = rtrim(get_setting('base_url'), '/');
        $pdfPath = $invoiceDir . $invoiceFileName;
        $pdfUrl = $baseUrl . "/invoices/" . $invoiceFileName;

        // Payment methods info for PDF
        $paymentInfoHtml = "";
        $waPaymentInfo = "";
        if ($payment_methods) {
            foreach ($payment_methods as $pm) {
                $paymentInfoHtml .= "<strong>{$pm['method_name']}:</strong> " . escape($pm['details']) . "<br>";
                $waPaymentInfo .= "{$pm['method_name']}: {$pm['details']}\n";
            }
        } else {
            $paymentInfoHtml = "<strong>Payment method will be shared by admin.</strong>";
            $waPaymentInfo = "Payment method will be shared by admin.";
        }

        // --- PDF HTML (Professional) ---
        $companyName = "APIFlair.com";
        $companyAddress = "Office #54, Tech Avenue, Hyderabad, Pakistan";
        $companyPhone = "+92 312 3898120";
        $companyEmail = "support@apiflair.com";
        $companyLogoUrl = $baseUrl . "/assets/logo.png"; // Place your logo in /assets/logo.png

        $html = '
        <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; }
        .invoice-box {
            max-width: 700px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 10px #eee;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .logo {
            height: 60px;
        }
        .company-info {
            text-align: right;
            font-size: 14px;
            color: #555;
        }
        .invoice-title {
            font-size: 32px;
            color: #2563eb;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .invoice-metadata {
            font-size: 15px;
            margin-bottom: 20px;
            color: #444;
        }
        .section-title {
            font-size: 18px;
            color: #2563eb;
            margin-top: 25px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .details-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .details-table td, .summary-table th, .summary-table td {
            padding: 8px 10px;
        }
        .details-table tr td:first-child {
            color: #555;
            width: 120px;
            font-weight: bold;
        }
        .summary-table th {
            background: #2563eb;
            color: #fff;
            border-radius: 6px 6px 0 0;
            font-size: 15px;
            text-align: left;
        }
        .summary-table td {
            background: #f6f8fa;
            border-bottom: 1px solid #e5e7eb;
            font-size: 15px;
        }
        .total-row td {
            background: #e0e7ff;
            color: #2563eb;
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #2563eb;
        }
        .payment-info {
            margin-top: 24px;
            font-size: 15px;
        }
        .status-box {
            display: inline-block;
            background: #ffe066;
            color: #b58300;
            padding: 4px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 8px;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #999;
            text-align: center;
        }
        </style>
        <div class="invoice-box">
            <div class="header">
                <div>
                    <img src="' . $companyLogoUrl . '" class="logo">
                </div>
                <div class="company-info">
                    <strong>' . $companyName . '</strong><br>
                    ' . $companyAddress . '<br>
                    ' . $companyPhone . '<br>
                    <a href="mailto:' . $companyEmail . '">' . $companyEmail . '</a>
                </div>
            </div>

            <div class="invoice-title">INVOICE</div>
            <div class="invoice-metadata">
                <strong>Invoice #:</strong> ' . $invoice_id . '<br>
                <strong>Date:</strong> ' . $date . '
            </div>

            <div class="section-title">Bill To</div>
            <table class="details-table">
                <tr><td>Name:</td><td>' . escape($name) . '</td></tr>
                <tr><td>Email:</td><td>' . escape($email) . '</td></tr>
                <tr><td>Phone:</td><td>' . escape($phone) . '</td></tr>
            </table>

            <div class="section-title">Order Summary</div>
            <table class="summary-table">
                <tr>
                    <th>Service</th>
                    <th>Accounts</th>
                    <th>Messages</th>
                    <th>Media</th>
                    <th>Amount (PKR)</th>
                </tr>
                <tr>
                    <td>' . escape($plan['name']) . '</td>
                    <td>' . escape($plan['accounts']) . '</td>
                    <td>' . escape($plan['messages']) . '</td>
                    <td>' . ($plan['media'] === 'Yes' ? "Included" : "Not Included") . '</td>
                    <td>' . number_format($plan['price_pkr']) . '</td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" align="right">Total</td>
                    <td>PKR ' . number_format($plan['price_pkr']) . '</td>
                </tr>
            </table>

            <div class="section-title">Payment</div>
            <div class="payment-info">
                ' . $paymentInfoHtml . '
                <br>
                <span class="status-box">Status: Unpaid</span>
            </div>

            <div class="footer">
                If you have any questions, contact us at ' . $companyEmail . ' or WhatsApp ' . $companyPhone . '<br>
                Thank you for your business!
            </div>
        </div>
        ';

        try {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

            // WhatsApp message with formatting
            $waMessage =
                "*Dear $name*,\n\n"
                . "Thank you for your order!\n"
                . "*Invoice:* $invoice_id\n"
                . "*Amount:* PKR " . number_format($plan['price_pkr']) . "\n\n"
                . "*Payment Methods:*\n"
                . $waPaymentInfo . "\n"
                . "Your invoice is attached. Kindly pay the amount and upload the payment proof on our portal.\n\n"
                . "If you need help, simply reply to this message.\n\n"
                . "*Thank you for choosing apiflair!*";

            // Send Invoice PDF via WhatsApp, with invoice number as file name
            $waSend = send_whatsapp_message($phone, $waMessage, $pdfPath);

            if (!$waSend['success']) {
                $errors[] = "Could not send invoice to WhatsApp: " . ($waSend['error'] ?? 'Unknown error');
            }
        } catch (Exception $ex) {
            $errors[] = "Failed to generate invoice PDF: " . $ex->getMessage();
        }

        // --- STORE ORDER IN DATABASE (insert after invoice is generated) ---
        if (empty($errors)) {
            try {
                // Check if order already exists (avoid duplicate on reload)
                $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE invoice_id = ?");
                $stmt->execute([$invoice_id]);
                $exists = $stmt->fetchColumn();
                if (!$exists) {
                    $stmt = $conn->prepare("INSERT INTO orders 
                        (invoice_id, user_id, name, email, phone, package, price, status, payment_status, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $invoice_id,
                        $user_id, // <-- store user_id (can be null for guest)
                        $name,
                        $email,
                        $phone,
                        $plan['name'],
                        $plan['price_pkr'],
                        'pending',   // status
                        'pending'    // payment_status
                    ]);
                }
            } catch (Exception $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
        // --- END DATABASE INSERT ---
    }
}

// Step 3: Payment upload
if (isset($_POST['submit_payment']) && isset($_FILES['screenshot'])) {
    $invoice_id = $_POST['invoice_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Validate upload
    if ($_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Screenshot upload failed.";
        $step = 2;
    } else {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['screenshot']['type'], $allowed)) {
            $errors[] = "Only JPG, PNG, or WEBP images allowed.";
            $step = 2;
        } else {
            // Move uploaded file
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $file_ext = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('pay_', true) . '.' . $file_ext;
            $dest_path = $upload_dir . $file_name;
            move_uploaded_file($_FILES['screenshot']['tmp_name'], $dest_path);

            // Update the existing order with screenshot file name and update payment_status
            try {
                $stmt = $conn->prepare("UPDATE orders SET screenshot=?, payment_status='submitted' WHERE invoice_id=?");
                $stmt->execute([$file_name, $invoice_id]);
            } catch (Exception $e) {
                $errors[] = "Database update error: " . $e->getMessage();
            }

            $orderCompleted = true;
            $step = 3;

            // --- Notify admin on payment screenshot upload ---
            if (empty($errors)) {
                $admin_phone = get_admin_whatsapp($conn);
                if ($admin_phone) {
                    $adminMsg =
                        "*Payment Proof Submitted*\n"
                        . "------------------------\n"
                        . "*Customer Name:* $name\n"
                        . "*Email:* $email\n"
                        . "*Phone:* $phone\n"
                        . "*Invoice ID:* $invoice_id\n"
                        . "------------------------\n"
                        . "A payment screenshot was uploaded and order placed. Please verify and activate the order in the admin panel.";

                    // Send WhatsApp message to admin (with screenshot if supported)
                    send_whatsapp_message($admin_phone, $adminMsg, $dest_path);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <?php include("head.php"); ?>

<body class="bg-gray-100 text-gray-900 min-h-screen">

<?php include("header.php"); ?>

<div class="max-w-2xl mx-auto mt-10 px-4">
    <div class="bg-white shadow-lg rounded-xl p-8">

        <!-- Stepper -->
        <div class="flex items-center mb-8">
            <div class="flex-shrink-0 w-8 h-8 rounded-full <?= $step >= 1 ? 'bg-indigo-600 text-white' : 'bg-indigo-200 text-indigo-700' ?> flex items-center justify-center font-bold">1</div>
            <div class="mx-3 <?= $step >= 1 ? 'text-indigo-600 font-semibold' : 'text-gray-400' ?>">Details</div>
            <div class="flex-grow border-t-2 border-indigo-200"></div>
            <div class="flex-shrink-0 w-8 h-8 rounded-full <?= $step >= 2 ? 'bg-indigo-600 text-white' : 'bg-indigo-200 text-indigo-700' ?> flex items-center justify-center font-bold">2</div>
            <div class="mx-3 <?= $step >= 2 ? 'text-indigo-600 font-semibold' : 'text-gray-400' ?>">Invoice</div>
            <div class="flex-grow border-t-2 border-indigo-200"></div>
            <div class="flex-shrink-0 w-8 h-8 rounded-full <?= $step === 3 ? 'bg-indigo-600 text-white' : 'bg-indigo-200 text-indigo-700' ?> flex items-center justify-center font-bold">3</div>
            <div class="ml-3 <?= $step === 3 ? 'text-indigo-600 font-semibold' : 'text-gray-400' ?>">Done</div>
        </div>

        <?php if ($errors): ?>
            <div class="bg-red-100 border border-red-300 text-red-700 rounded-md px-4 py-3 mb-6">
                <?php foreach ($errors as $err): ?>
                    <div><?= escape($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Details Form -->
        <?php if ($step === 1): ?>
            <div class="bg-indigo-50 rounded-lg p-4 mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-indigo-700">WhatsApp Messaging Service</h2>
                    <ul class="text-sm text-indigo-900 mt-1">
                        <li><strong><?= escape($plan['accounts']) ?></strong></li>
                        <li><?= escape($plan['messages']) ?><?= $plan['media'] === 'Yes' ? ' + Media' : '' ?></li>
                        <li class="mt-1 text-green-700 font-semibold">PKR <?= number_format($plan['price_pkr']) ?></li>
                    </ul>
                </div>
            </div>
            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= escape($name) ?>" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           maxlength="64" placeholder="Your Name">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?= escape($email) ?>" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           maxlength="64" placeholder="you@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone (923XXXXXXXXX format) <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="<?= escape($phone) ?>" required pattern="923[0-9]{9}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           maxlength="12" placeholder="923001234567">
                </div>
                <div class="flex items-center text-xs text-gray-600">
                    <input type="checkbox" name="agree" required class="mr-2">
                    I agree to the <a href="terms" class="text-indigo-600 hover:underline mx-1">Terms</a> and <a href="privacy" class="text-indigo-600 hover:underline ml-1">Privacy Policy</a>
                </div>
                <button type="submit" name="proceed_to_invoice"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-lg font-semibold transition duration-200">
                    Next: Checkout & Generate Invoice
                </button>
            </form>
        <?php endif; ?>

        <?php if ($step === 2):
            if (!isset($invoice_id) || !$invoice_id) {
                $invoice_id = "INV-" . date("YmdHis");
                $date = date("d M, Y");
                $baseUrl = rtrim(get_setting('base_url'), '/');
                $pdfUrl = $baseUrl . "/invoices/" . $invoice_id . ".pdf";
            }
        ?>
            <h2 class="text-2xl font-bold mb-6 text-indigo-600">🧾 Checkout & Upload Payment</h2>
            <div class="bg-yellow-50 p-4 rounded-md mb-6 border border-yellow-200">
                <p><strong>Invoice ID:</strong> <?= $invoice_id ?></p>
                <p><strong>Date:</strong> <?= $date ?></p>
                <p><strong>Status:</strong> <span class="inline-block bg-yellow-300 text-yellow-900 text-xs px-2 py-1 rounded">Unpaid</span></p>
                <?php if ($payment_methods): ?>
                    <p><strong>Payment Methods:</strong></p>
                    <ul class="list-disc ml-6 text-gray-800">
                        <?php foreach ($payment_methods as $pm): ?>
                            <li>
                                <span class="font-medium"><?= escape($pm['method_name']) ?>:</span>
                                <?= escape($pm['details']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><strong>Payment method will be shared by admin.</strong></p>
                <?php endif; ?>
                <p><strong>Your Invoice PDF:</strong> <a href="<?= escape($pdfUrl) ?>" class="underline text-indigo-600" target="_blank">Download</a></p>
            </div>
            <div class="mb-5">
                <h3 class="text-lg font-semibold mb-2 text-gray-700">🙍 Customer</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li><strong>Name:</strong> <?= escape($name) ?></li>
                    <li><strong>Email:</strong> <?= escape($email) ?></li>
                    <li><strong>Phone:</strong> <?= escape($phone) ?></li>
                </ul>
            </div>
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2 text-gray-700">📦 Service</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li><strong>Service:</strong> <?= $plan['name'] ?> (<?= $plan['accounts'] ?>)</li>
                    <li><strong>Messages:</strong> <?= $plan['messages'] ?><?= $plan['media'] === 'Yes' ? ' + Media' : '' ?></li>
                    <li><strong>Amount:</strong> <span class="text-green-600 font-medium">PKR <?= number_format($plan['price_pkr']) ?></span></li>
                </ul>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Pay & Upload Screenshot <span class="text-red-500">*</span></label>
                    <input type="file" name="screenshot" accept="image/png,image/jpeg,image/webp"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <!-- Hidden Inputs -->
                <input type="hidden" name="name" value="<?= escape($name) ?>">
                <input type="hidden" name="email" value="<?= escape($email) ?>">
                <input type="hidden" name="phone" value="<?= escape($phone) ?>">
                <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">
                <button type="submit" name="submit_payment"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md text-lg font-medium transition duration-200">
                    Submit & Activate
                </button>
            </form>
        <?php endif; ?>

        <!-- Step 3: Success -->
        <?php if ($orderCompleted): ?>
            <div class="text-center py-10">
                <div class="text-4xl mb-4 text-green-600">✔️</div>
                <h2 class="text-2xl font-bold mb-2">Order Placed Successfully!</h2>
                <p class="mb-4">Thank you! Your order and payment screenshot have been submitted.<br>
                Your account will be activated after verification.<br>
                <a href="account" class="text-indigo-600 underline">Go to Dashboard</a>
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>