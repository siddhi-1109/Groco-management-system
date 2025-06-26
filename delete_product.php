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
$product = $conn->query("SELECT image FROM products WHERE id = $id")->fetch_assoc();
if ($product && $product['image'] && file_exists($product['image'])) {
    unlink($product['image']);
}
$conn->query("DELETE FROM products WHERE id = $id");
header("Location: list_products.php");
exit;
?> 