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
$conn->query("DELETE FROM categories WHERE id = $id");
header("Location: list_categories.php");
exit;
?> 