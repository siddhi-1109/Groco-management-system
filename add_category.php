<?php
include 'protect.php';
include 'db.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        $success = "Category added successfully!";
    } else {
        $error = "Failed to add category. It may already exist.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4" style="max-width:500px;">
        <h2 class="mb-4">Add Category</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <button class="btn btn-primary" type="submit">Add Category</button>
            <a href="list_categories.php" class="btn btn-secondary">Back to List</a>
        </form>
    </div>
</div>
</body>
</html> 