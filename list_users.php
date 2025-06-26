<?php
include 'protect.php';
include 'db.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}
$result = $conn->query("SELECT * FROM users");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4">
        <h2>Users</h2>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                        <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 