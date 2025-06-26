<?php
// sidebar.php
$role = $_SESSION['role'] ?? '';
?>
<!-- Sidebar: fixed, stable, professional -->
<div class="d-flex flex-column flex-shrink-0 p-3 bg-light sidebar" style="width: 220px; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1030; background: #fff; box-shadow: 2px 0 5px rgba(0,0,0,0.05);">
  <a href="<?= $role === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php' ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
    <?php if ($role === 'admin'): ?>
      <i class="bi bi-shop fs-3 me-2"></i>
      <span class="fs-4 fw-bold">Grocery Admin</span>
    <?php else: ?>
      <i class="bi bi-person-circle fs-3 me-2"></i>
      <span class="fs-4 fw-bold">Grocery User</span>
    <?php endif; ?>
  </a>
  <hr>
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
      <a href="<?= $role === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php' ?>" class="nav-link">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
      </a>
    </li>
    <li>
      <a href="list_products.php" class="nav-link">
        <i class="bi bi-box-seam me-2"></i> Products
      </a>
    </li>
    <li>
      <a href="list_categories.php" class="nav-link">
        <i class="bi bi-tags me-2"></i> Categories
      </a>
    </li>
    <?php if ($role === 'admin'): ?>
    <li>
      <a href="stock_record.php" class="nav-link">
        <i class="bi bi-clipboard-data me-2"></i> Stock Record
      </a>
    </li>
    <?php endif; ?>
    <?php if ($role === 'user'): ?>
    <li>
      <a href="wishlist.php" class="nav-link">
        <i class="bi bi-heart me-2"></i> My Wishlist
      </a>
    </li>
    <li>
      <a href="my_orders.php" class="nav-link">
        <i class="bi bi-receipt me-2"></i> My Orders
      </a>
    </li>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
    <li>
      <a href="list_users.php" class="nav-link">
        <i class="bi bi-people me-2"></i> Users
      </a>
    </li>
    <?php endif; ?>
    <li>
      <a href="logout.php" class="nav-link text-danger">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
      </a>
    </li>
  </ul>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
.sidebar {
  width: 220px;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1030;
  background: #fff;
  box-shadow: 2px 0 5px rgba(0,0,0,0.05);
}
.main-content {
  margin-left: 220px;
  transition: margin-left 0.2s;
}
@media (max-width: 768px) {
  .sidebar {
    position: static;
    width: 100%;
    height: auto;
  }
  .main-content {
    margin-left: 0 !important;
  }
}
.container, .container-fluid {
  padding-top: 2rem;
}
</style> 