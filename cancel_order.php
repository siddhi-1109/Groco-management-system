<?php
include 'protect.php';
include 'db.php';
$user_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id'] ?? 0);
if ($order_id) {
    $order = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();
    if ($order && in_array($order['status'], ['pending','processing'])) {
        $conn->query("UPDATE orders SET status = 'cancelled' WHERE id = $order_id");
        header("Location: my_orders.php?msg=cancelled");
        exit;
    }
}
header("Location: my_orders.php?msg=error");
exit; 