<?php
include 'protect.php';
include 'db.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}
if (!isset($_GET['id'])) {
    header("Location: list_categories.php");
    exit;
}
$id = intval($_GET['id']);
$category = $conn->query("SELECT * FROM categories WHERE id = $id")->fetch_assoc();
if (!$category) {
    header("Location: list_categories.php");
    exit;
}
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    if ($stmt->execute()) {
        $success = "Category updated successfully!";
        $category = $conn->query("SELECT * FROM categories WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Failed to update category.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4" style="max-width:500px;">
        <h2 class="mb-4">Edit Category</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Update Category</button>
            <a href="list_categories.php" class="btn btn-secondary">Back to List</a>
        </form>
    </div>
</div>
</body>
</html> 