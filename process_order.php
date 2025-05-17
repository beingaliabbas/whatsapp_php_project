<?php include("header.php"); ?> 
<?php
include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name        = trim($_POST['name'] ?? '');
  $email       = trim($_POST['email'] ?? '');
  $phone       = trim($_POST['phone'] ?? '');
  $package     = trim($_POST['package'] ?? '');
  $price       = trim($_POST['price'] ?? '');
  $invoice_id  = trim($_POST['invoice_id'] ?? '');
  $status      = 'pending';
  $created_at  = date('Y-m-d H:i:s');

  // Validate required fields
  if (!$name || !$email || !$package || !$price || !$invoice_id) {
    exit("<div class='text-center mt-10 text-red-600 font-medium'>❌ Missing required fields.</div>");
  }

  // Handle file upload
  $upload_dir = 'uploads/';
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
  }

  if (!empty($_FILES['screenshot']['name'])) {
    $filename = basename($_FILES['screenshot']['name']);
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed)) {
      exit("<div class='text-center mt-10 text-red-600 font-medium'>❌ Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.</div>");
    }

    $new_filename = uniqid('ss_', true) . '.' . $extension;
    $upload_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $upload_path)) {
      exit("<div class='text-center mt-10 text-red-600 font-medium'>❌ Failed to upload file.</div>");
    }
  } else {
    exit("<div class='text-center mt-10 text-red-600 font-medium'>❌ Please upload your payment screenshot.</div>");
  }

  // Save to database
  try {
    $stmt = $conn->prepare("INSERT INTO orders (name, email, phone, package, price, invoice_id, screenshot, status, created_at)
                            VALUES (:name, :email, :phone, :package, :price, :invoice_id, :screenshot, :status, :created_at)");

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':package', $package);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':invoice_id', $invoice_id);
    $stmt->bindParam(':screenshot', $new_filename);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':created_at', $created_at);

    $stmt->execute();

    // ✅ Tailwind-styled thank you message
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Order Received</title>
      <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
    </head>
    <body class='bg-gray-50'>
    
      <div class='max-w-xl mx-auto mt-20 bg-white shadow-lg rounded-lg p-8 text-center'>
        <h2 class='text-2xl font-bold text-green-600 mb-4'>🎉 Thank You, $name!</h2>
        <p class='text-gray-700 mb-2'>Your order has been received and is pending verification.</p>
        <p class='text-sm text-gray-600'><strong>Invoice ID:</strong> $invoice_id</p>
        <p class='text-sm text-gray-600 mb-2'><strong>Payment Method:</strong> Easypaisa (03251387814 - Ali Abbas)</p>
        <p class='text-sm text-gray-600'>We will activate your plan shortly after confirming payment.</p>
        <a href='index' class='mt-6 inline-block px-5 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition'>Back to Home</a>
      </div>
    </body>
    </html>
    ";

  } catch (PDOException $e) {
    echo "<div class='text-center mt-10 text-red-600 font-medium'>❌ Database error: " . $e->getMessage() . "</div>";
  }
} else {
  echo "<div class='text-center mt-10 text-red-600 font-medium'>❌ Invalid request.</div>";
}

?>
