<?php
include 'protect.php';
include 'db.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}
if (!isset($_GET['id'])) {
    header("Location: list_products.php");
    exit;
}
$id = intval($_GET['id']);
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
if (!$product) {
    header("Location: list_products.php");
    exit;
}
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $image = $target_dir . uniqid() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    }
    $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, stock=?, image=? WHERE id=?");
    $stmt->bind_param("siddsi", $name, $category_id, $price, $stock, $image, $id);
    if ($stmt->execute()) {
        $success = "Product updated successfully!";
        $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Failed to update product.";
    }
}
$categories = $conn->query("SELECT id, name FROM categories");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4" style="max-width:600px;">
        <h2 class="mb-4">Edit Product</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" name="price" class="form-control" min="0" step="0.01" value="<?= $product['price'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" min="0" value="<?= $product['stock'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Product Image</label>
                <?php if ($product['image']): ?>
                    <div class="mb-2">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image" style="max-width:120px; max-height:120px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small class="text-muted">Leave blank to keep current image.</small>
            </div>
            <button class="btn btn-primary" type="submit">Update Product</button>
            <a href="list_products.php" class="btn btn-secondary">Back to List</a>
        </form>
    </div>
</div>
</body>
</html> 