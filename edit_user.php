<?php
include 'protect.php';
include 'db.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}
if (!isset($_GET['id'])) {
    header("Location: list_users.php");
    exit;
}
$id = intval($_GET['id']);
$user = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
if (!$user) {
    header("Location: list_users.php");
    exit;
}
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $hashed_password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, role=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $role, $id);
    }
    if ($stmt->execute()) {
        $success = "User updated successfully!";
        $user = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Failed to update user.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4" style="max-width:500px;">
        <h2 class="mb-4">Edit User</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button class="btn btn-primary" type="submit">Update User</button>
            <a href="list_users.php" class="btn btn-secondary">Back to List</a>
        </form>
    </div>
</div>
</body>
</html> 