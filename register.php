<?php
include 'db.php';

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    // Check for duplicate username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = "Username already exists.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);
        if ($stmt->execute()) {
            $success = "Registration successful! Redirecting to login...";
            header("refresh:2;url=login.php");
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register - Grocery Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:400px;">
    <h2 class="mb-4 text-center">Register</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>
        <button class="btn btn-primary w-100" type="submit">Register</button>
        <div class="mt-3 text-center">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </form>
</div>
</body>
</html> 