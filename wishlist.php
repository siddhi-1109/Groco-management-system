<?php
include 'protect.php';
include 'db.php';
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
// Remove from wishlist
if (isset($_POST['remove'])) {
    $pid = intval($_POST['remove']);
    $conn->query("DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $pid");
    $success = 'Removed from wishlist.';
}
// Add to cart
if (isset($_POST['add_to_cart'])) {
    $pid = intval($_POST['add_to_cart']);
    $exists = $conn->query("SELECT id FROM cart_items WHERE user_id = $user_id AND product_id = $pid")->fetch_assoc();
    if ($exists) {
        $conn->query("UPDATE cart_items SET quantity = quantity + 1 WHERE id = {$exists['id']}");
        $success = 'Product quantity increased in cart.';
    } else {
        $conn->query("INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $pid, 1)");
        $success = 'Product added to cart.';
    }
}
// Fetch wishlist products
$result = $conn->query("SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = $user_id");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Wishlist</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'navbar.php'; ?>
  <div class="container py-4" style="max-width:900px;">
    <h2 class="mb-4">My Wishlist</h2>
    <?php if ($success): ?>
      <div class="alert alert-success"> <?= $success ?> </div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"> <?= $error ?> </div>
    <?php endif; ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="col">
        <div class="card h-100 shadow-sm">
          <img src="<?= $row['image'] ?: 'default-product.png' ?>" class="card-img-top" style="height:180px;object-fit:cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1"><?= htmlspecialchars($row['name']) ?></h5>
            <p class="card-text mb-1 text-muted">₹<?= number_format($row['price'], 2) ?></p>
            <p class="card-text mb-1 small">Stock: <?= $row['stock'] ?></p>
            <div class="mt-auto d-flex justify-content-between align-items-center">
              <form method="post" class="d-inline">
                <input type="hidden" name="remove" value="<?= $row['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit">Remove</button>
              </form>
              <form method="post" class="d-inline">
                <input type="hidden" name="add_to_cart" value="<?= $row['id'] ?>">
                <button class="btn btn-primary btn-sm" type="submit">Add to Cart</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
    </div>
    <?php if ($result->num_rows == 0): ?>
      <div class="alert alert-info mt-4">Your wishlist is empty.</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html> 