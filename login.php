<?php
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            // Role-based redirect
            if ($row['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Grocery Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:400px;">
    <h2 class="mb-4 text-center">Login</h2>
    <?php if ($error): ?>
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
        <button class="btn btn-primary w-100" type="submit">Login</button>
        <div class="mt-3 text-center">
            <a href="register.php">Don't have an account? Register</a>
        </div>
    </form>
</div>
</body>
</html> 