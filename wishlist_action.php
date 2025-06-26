<?php
include 'protect.php';
include 'db.php';
$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
$redirect = 'user_dashboard.php';
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'user_dashboard.php') !== false) {
    $redirect = $_SERVER['HTTP_REFERER'];
}
if ($action && $product_id) {
    if ($action === 'add') {
        $exists = $conn->query("SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id")->fetch_assoc();
        if (!$exists) {
            $conn->query("INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)");
        }
    } elseif ($action === 'remove') {
        $conn->query("DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $product_id");
    }
}
header("Location: $redirect");
exit; 