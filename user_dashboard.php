<?php
// USER DASHBOARD: Only accessible by users with role 'user'
include 'protect.php';
if ($_SESSION['role'] !== 'user') {
    header("Location: admin_dashboard.php");
    exit;
}
include 'db.php';
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $pid = intval($_POST['add_to_cart']);
    $exists = $conn->query("SELECT id FROM cart_items WHERE user_id = $user_id AND product_id = $pid")->fetch_assoc();
    if ($exists) {
        $conn->query("UPDATE cart_items SET quantity = quantity + 1 WHERE id = {$exists['id']}");
        $success = 'Product quantity increased in cart.';
    } else {
        $conn->query("INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $pid, 1)");
        $success = 'Product added to cart.';
    }
}
// Fetch stats
$user_wishlist_count = $conn->query("SELECT COUNT(*) FROM wishlist WHERE user_id = $user_id")->fetch_row()[0];
// Fetch wishlist product IDs
$wishlist_ids = [];
$res = $conn->query("SELECT product_id FROM wishlist WHERE user_id = $user_id");
while ($row = $res->fetch_assoc()) {
    $wishlist_ids[] = $row['product_id'];
}
// Fetch cart item count
$cart_count = $conn->query("SELECT SUM(quantity) FROM cart_items WHERE user_id = $user_id")->fetch_row()[0] ?? 0;
$search = trim($_GET['search'] ?? '');
$filter_sql = $search ? "WHERE p.name LIKE '%" . $conn->real_escape_string($search) . "%'" : '';
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $filter_sql");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - Grocery Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body { background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); min-height: 100vh; }
    .dashboard-card { min-height: 120px; }
    .main-content { margin-left: 220px; }
    @media (max-width: 768px) {
      .main-content { margin-left: 0 !important; }
    }
    .product-card {
      transition: box-shadow 0.2s, transform 0.2s;
      border-radius: 1rem;
      border: none;
      box-shadow: 0 4px 24px 0 rgba(80, 80, 160, 0.08);
    }
    .product-card:hover {
      box-shadow: 0 8px 32px 0 rgba(80,80,160,0.16);
      transform: translateY(-4px) scale(1.03);
    }
    .product-img {
      object-fit: cover;
      height: 180px;
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
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
    .search-bar {
      max-width: 400px;
      border-radius: 2rem;
      padding-left: 2.5rem;
      background: #fff;
      box-shadow: 0 2px 8px 0 rgba(80, 80, 160, 0.04);
      position: relative;
    }
    .search-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #6366f1;
      font-size: 1.2rem;
    }
    .btn-gradient {
      background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%);
      color: #fff;
      border: none;
      box-shadow: 0 2px 8px 0 rgba(80, 80, 160, 0.08);
      border-radius: 2rem;
      transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-gradient:hover {
      background: linear-gradient(90deg, #60a5fa 0%, #6366f1 100%);
      color: #fff;
      box-shadow: 0 4px 16px 0 rgba(80, 80, 160, 0.12);
    }
    .badge-new {
      background: #22d3ee;
      color: #fff;
      font-size: 0.75rem;
      margin-left: 0.5rem;
    }
    .badge-low {
      background: #ef4444;
      color: #fff;
      font-size: 0.75rem;
      margin-left: 0.5rem;
    }
    .empty-state {
      text-align: center;
      margin-top: 3rem;
      color: #6366f1;
    }
    .empty-state img {
      max-width: 220px;
      opacity: 0.7;
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'navbar.php'; ?>
  <div class="container-fluid py-4">
    <div class="welcome-banner mb-4">
      <h2 class="fw-bold mb-1">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
      <p class="mb-0">Explore products and add them to your cart or wishlist.</p>
    </div>
    <div class="d-flex justify-content-end mb-3 gap-2">
      <a href="cart.php" class="btn btn-gradient position-relative">
        <i class="bi bi-cart me-1"></i> Go to Cart
        <?php if ($cart_count > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= $cart_count ?>
          </span>
        <?php endif; ?>
      </a>
      <a href="my_orders.php" class="btn btn-gradient"><i class="bi bi-receipt me-1"></i> My Orders</a>
    </div>
    <form class="d-flex mb-4 position-relative" method="get">
      <span class="search-icon"><i class="bi bi-search"></i></span>
      <input class="form-control me-2 search-bar" type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search products..." aria-label="Search">
      <button class="btn btn-outline-primary ms-2" type="submit">Search</button>
    </form>
    <?php if ($success): ?>
      <div class="alert alert-success"> <?= $success ?> </div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"> <?= $error ?> </div>
    <?php endif; ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php $hasProducts = false; while($row = $result->fetch_assoc()): $hasProducts = true; ?>
      <div class="col">
        <div class="card h-100 shadow-sm product-card">
          <img src="<?= $row['image'] ?: 'default-product.png' ?>" class="card-img-top product-img" alt="Product Image">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1">
              <?= htmlspecialchars($row['name']) ?>
              <?php if (strtotime($row['created_at'] ?? '') > strtotime('-7 days')): ?>
                <span class="badge badge-new">New</span>
              <?php endif; ?>
              <?php if ($row['stock'] < 5): ?>
                <span class="badge badge-low">Low Stock</span>
              <?php endif; ?>
            </h5>
            <p class="card-text mb-1 text-muted">₹<?= number_format($row['price'], 2) ?></p>
            <p class="card-text mb-1 small">Stock: <?= $row['stock'] ?>
              <?php if ($row['stock'] <= 0): ?>
                <span class="badge bg-danger ms-2">Unavailable</span>
              <?php endif; ?>
            </p>
            <p class="card-text mb-1 small text-secondary">Category: <?= htmlspecialchars($row['category_name']) ?></p>
            <div class="mt-auto d-flex justify-content-between align-items-center">
              <form method="post" action="wishlist_action.php" class="d-inline">
                <input type="hidden" name="action" value="<?= in_array($row['id'], $wishlist_ids) ? 'remove' : 'add' ?>">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm" title="<?= in_array($row['id'], $wishlist_ids) ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                  <i class="bi <?= in_array($row['id'], $wishlist_ids) ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
                </button>
              </form>
              <form method="post" class="d-inline">
                <input type="hidden" name="add_to_cart" value="<?= $row['id'] ?>">
                <button class="btn btn-primary btn-sm" type="submit" <?= $row['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
    </div>
    <?php if (!$hasProducts): ?>
      <div class="empty-state">
        <img src="https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/shopping-cart.svg" alt="No products">
        <h4 class="mt-3">No products found</h4>
        <p>Try adjusting your search or check back later!</p>
      </div>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 