<?php
include 'protect.php';
include 'db.php';
$role = $_SESSION['role'] ?? '';
$result = $conn->query("SELECT * FROM categories");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4">
        <h2>Categories</h2>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <?php if ($role === 'admin'): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <?php if ($role === 'admin'): ?>
                    <td>
                        <a href="edit_category.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="delete_category.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
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