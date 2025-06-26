<?php
include 'protect.php';
include 'db.php';
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'navbar.php'; ?>
  <div class="container py-4" style="max-width:900px;">
    <h2 class="mb-4">My Orders</h2>
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Total</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php $i=1; while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= $row['order_date'] ?></td>
          <td>₹<?= number_format($row['total'], 2) ?></td>
          <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span></td>
          <td>
            <a href="order_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">View Details</a>
            <?php if (in_array($row['status'], ['pending','processing'])): ?>
              <form method="post" action="cancel_order.php" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                <button class="btn btn-sm btn-danger" type="submit">Cancel Order</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <?php if ($result->num_rows == 0): ?>
      <div class="alert alert-info mt-4">You have not placed any orders yet.</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html> 