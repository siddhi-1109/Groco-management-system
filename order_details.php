<?php
include 'protect.php';
include 'db.php';
$order_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
// Fetch order
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$order || ($role !== 'admin' && $order['user_id'] != $user_id)) {
    header("Location: my_orders.php");
    exit;
}
// Update status (admin only)
if ($role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    $conn->query("UPDATE orders SET status = '" . $conn->real_escape_string($status) . "' WHERE id = $order_id");
    $order['status'] = $status;
}
// Fetch order items
$items = $conn->query("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'navbar.php'; ?>
  <div class="container py-4" style="max-width:900px;">
    <h2 class="mb-4">Order Details</h2>
    <div class="mb-3">
      <strong>Order Date:</strong> <?= $order['order_date'] ?><br>
      <strong>Total:</strong> ₹<?= number_format($order['total'], 2) ?><br>
      <strong>Status:</strong>
      <?php if ($role === 'admin'): ?>
        <form method="post" class="d-inline">
          <select name="status" class="form-select d-inline w-auto" onchange="this.form.submit()">
            <?php foreach (["pending","processing","shipped","delivered","cancelled"] as $status): ?>
              <option value="<?= $status ?>" <?= $order['status'] == $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      <?php else: ?>
        <span class="badge bg-secondary"><?= htmlspecialchars($order['status']) ?></span>
      <?php endif; ?>
      <?php if ($role !== 'admin' && in_array($order['status'], ['pending','processing'])): ?>
        <form method="post" action="cancel_order.php" class="d-inline" onsubmit="return confirm('Cancel this order?');">
          <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
          <button class="btn btn-danger" type="submit">Cancel Order</button>
        </form>
      <?php endif; ?>
    </div>
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Product</th>
          <th>Image</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
      <?php while($item = $items->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><img src="<?= $item['image'] ?: 'default-product.png' ?>" style="height:60px;width:60px;object-fit:cover;"></td>
          <td>₹<?= number_format($item['price'], 2) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <a href="my_orders.php" class="btn btn-secondary">Back to Orders</a>
  </div>
</div>
</body>
</html> 