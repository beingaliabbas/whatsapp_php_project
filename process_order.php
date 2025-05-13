<?php
include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name        = $_POST['name'] ?? '';
  $email       = $_POST['email'] ?? '';
  $phone       = $_POST['phone'] ?? '';
  $package     = $_POST['package'] ?? '';
  $price       = $_POST['price'] ?? '';
  $invoice_id  = $_POST['invoice_id'] ?? '';
  $status      = 'pending';
  $created_at  = date('Y-m-d H:i:s');

  // Validate required fields
  if (empty($name) || empty($email) || empty($package) || empty($price) || empty($invoice_id)) {
    die("Missing required fields.");
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
      die("Invalid file type. Only JPG, JPEG, PNG, and WEBP allowed.");
    }

    $new_filename = uniqid('ss_', true) . '.' . $extension;
    $upload_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $upload_path)) {
      die("Failed to upload file.");
    }
  } else {
    die("Please upload your payment screenshot.");
  }

  // Save to database (PDO)
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

    echo "<div style='padding:40px;text-align:center;font-family:sans-serif;'>
            <h2>🎉 Thank You, $name!</h2>
            <p>Your order has been received and is pending verification.</p>
            <p><strong>Invoice ID:</strong> $invoice_id</p>
            <p><strong>Payment Method:</strong> Easypaisa (03251387814 - Ali Abbas)</p>
            <p>We will activate your plan shortly after confirming payment.</p>
            <a href='index.php' style='margin-top:20px;display:inline-block;padding:10px 20px;background:#4f46e5;color:#fff;border-radius:6px;text-decoration:none;'>Back to Home</a>
          </div>";
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
} else {
  echo "Invalid request.";
}
?>
