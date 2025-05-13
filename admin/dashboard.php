<?php
include("includes/header.php");
include("../db.php");

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $conn->query("SELECT SUM(price) FROM orders WHERE status='paid'")->fetchColumn();
$total_sessions = $conn->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
?>

<div class="container mt-4">
  <h2 class="mb-4">Admin Dashboard</h2>
  <div class="row g-4">
    <div class="col-md-3">
      <div class="card border-primary shadow-sm">
        <div class="card-body">
          <h5 class="card-title">👤 Users</h5>
          <p class="card-text fs-4 fw-bold"><?= $total_users ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-success shadow-sm">
        <div class="card-body">
          <h5 class="card-title">🧾 Orders</h5>
          <p class="card-text fs-4 fw-bold"><?= $total_orders ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-warning shadow-sm">
        <div class="card-body">
          <h5 class="card-title">💸 Revenue (PKR)</h5>
          <p class="card-text fs-4 fw-bold"><?= number_format($total_revenue) ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-info shadow-sm">
        <div class="card-body">
          <h5 class="card-title">🔌 Active Sessions</h5>
          <p class="card-text fs-4 fw-bold"><?= $total_sessions ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>
