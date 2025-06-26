<?php
include 'protect.php';
include 'db.php';

// Get stats
$total_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$total_categories = $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$low_stock = $conn->query("SELECT COUNT(*) FROM products WHERE stock < 5")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$wishlist_count = 0;
if ($conn->query("SHOW TABLES LIKE 'wishlist'")->num_rows) {
    $wishlist_count = $conn->query("SELECT COUNT(*) FROM wishlist")->fetch_row()[0];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard - Grocery Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar { box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
        .dashboard-card { min-height: 120px; }
        .main-content { margin-left: 220px; }
        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid py-4">
        <h2 class="mb-4">Dashboard</h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card dashboard-card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <h2><?= $total_products ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Wishlist Products</h5>
                        <h2><?= $wishlist_count ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Categories</h5>
                        <h2><?= $total_categories ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock</h5>
                        <h2><?= $low_stock ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <h2><?= $total_users ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 