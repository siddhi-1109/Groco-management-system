<?php
// ADMIN DASHBOARD: Only accessible by users with role 'admin'
include 'protect.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}
include 'db.php';
// Fetch stats
$total_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$total_categories = $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$low_stock = $conn->query("SELECT COUNT(*) FROM products WHERE stock < 5")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$wishlist_count = $conn->query("SELECT COUNT(*) FROM wishlist")->fetch_row()[0];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Grocery Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
      min-height: 100vh;
    }
    .dashboard-card {
      min-height: 120px;
      border: none;
      border-radius: 1rem;
      box-shadow: 0 4px 24px 0 rgba(80, 80, 160, 0.08);
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .dashboard-card:hover {
      transform: translateY(-4px) scale(1.03);
      box-shadow: 0 8px 32px 0 rgba(80, 80, 160, 0.16);
    }
    .dashboard-icon {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      opacity: 0.85;
    }
    .main-content { margin-left: 220px; }
    @media (max-width: 768px) {
      .main-content { margin-left: 0 !important; }
    }
    .dashboard-title {
      font-weight: 700;
      letter-spacing: 1px;
      color: #3730a3;
    }
    .dashboard-section {
      margin-bottom: 2.5rem;
    }
    .welcome-banner {
      background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%);
      color: #fff;
      border-radius: 1rem;
      padding: 2rem 2rem 1rem 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 24px 0 rgba(80, 80, 160, 0.08);
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }
    .table {
      border-radius: 1rem;
      overflow: hidden;
    }
    .table-striped > tbody > tr:nth-of-type(odd) {
      background-color: #f3f4f6;
    }
    .badge-status {
      font-size: 0.9em;
      padding: 0.5em 1em;
      border-radius: 1rem;
    }
    .chart-container {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 2px 8px 0 rgba(80, 80, 160, 0.04);
      padding: 2rem;
      margin-bottom: 2rem;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'navbar.php'; ?>
  <div class="container-fluid py-4">
    <div class="welcome-banner mb-4">
      <h2 class="fw-bold mb-1">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
      <p class="mb-0">Here's a quick overview and analytics for your grocery business.</p>
    </div>
    <div class="row g-4 dashboard-section">
      <div class="col-md-3">
        <div class="card dashboard-card text-center bg-primary text-white">
          <div class="card-body">
            <i class="bi bi-box-seam dashboard-icon"></i>
            <h5 class="card-title">Total Products</h5>
            <h2><?= $total_products ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card dashboard-card text-center bg-success text-white">
          <div class="card-body">
            <i class="bi bi-tags dashboard-icon"></i>
            <h5 class="card-title">Categories</h5>
            <h2><?= $total_categories ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card dashboard-card text-center bg-info text-white">
          <div class="card-body">
            <i class="bi bi-people dashboard-icon"></i>
            <h5 class="card-title">Users</h5>
            <h2><?= $total_users ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card dashboard-card text-center bg-danger text-white">
          <div class="card-body">
            <i class="bi bi-heart dashboard-icon"></i>
            <h5 class="card-title">Wishlist Products</h5>
            <h2><?= $wishlist_count ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3 mt-4">
        <div class="card dashboard-card text-center bg-warning text-dark">
          <div class="card-body">
            <i class="bi bi-exclamation-triangle dashboard-icon"></i>
            <h5 class="card-title">Low Stock Alerts</h5>
            <h2><?= $low_stock ?></h2>
          </div>
        </div>
      </div>
    </div>
    <div class="chart-container">
      <h5 class="mb-3">Sales Overview (Demo Data)</h5>
      <canvas id="salesChart" height="80"></canvas>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold">Product Inventory</h4>
      <a href="add_product.php" class="btn btn-success shadow-sm">Add Product</a>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover align-middle bg-white rounded shadow-sm">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
        $i = 1;
        while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['category_name']) ?></td>
            <td>₹<?= number_format($row['price'], 2) ?></td>
            <td class="<?= $row['stock'] < 5 ? 'text-danger fw-bold' : '' ?>">
              <?= $row['stock'] ?>
              <?php if ($row['stock'] < 5): ?>
                <span class="badge badge-status bg-danger ms-2">Low</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm me-1"><i class="bi bi-pencil"></i> Edit</a>
              <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i> Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    datasets: [{
      label: 'Sales',
      data: [12, 19, 3, 5, 2, 3, 9], // Demo data
      borderColor: '#6366f1',
      backgroundColor: 'rgba(99,102,241,0.1)',
      tension: 0.4,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: { beginAtZero: true }
    }
  }
});
</script>
</body>
</html>