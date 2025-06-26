<?php
include 'protect.php';
include 'db.php';
$role = $_SESSION['role'] ?? '';
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Product List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4">
        <h2>Product Inventory</h2>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <?php if ($role === 'admin'): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td>₹<?= number_format($row['price'], 2) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <?php if ($role === 'admin'): ?>
                    <td>
                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 