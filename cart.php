<?php
include 'protect.php';
include 'db.php';
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
// Update quantity or remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        if (isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $id => $qty) {
                $qty = max(1, intval($qty));
                $conn->query("UPDATE cart_items SET quantity = $qty WHERE id = $id AND user_id = $user_id");
            }
            $success = 'Cart updated.';
        }
    } elseif (isset($_POST['remove'])) {
        $id = intval($_POST['remove']);
        $conn->query("DELETE FROM cart_items WHERE id = $id AND user_id = $user_id");
        $success = 'Item removed.';
    } elseif (isset($_POST['checkout'])) {
        // Calculate total
        $items = $conn->query("SELECT ci.*, p.price, p.stock FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = $user_id");
        $total = 0; $ok = true;
        $cart = [];
        while ($row = $items->fetch_assoc()) {
            if ($row['stock'] < $row['quantity']) {
                $ok = false;
                $error = 'Insufficient stock for ' . htmlspecialchars($row['product_id']);
                break;
            }
            $total += $row['price'] * $row['quantity'];
            $cart[] = $row;
        }
        if ($ok && $cart) {
            $conn->begin_transaction();
            try {
                $conn->query("INSERT INTO orders (user_id, total) VALUES ($user_id, $total)");
                $order_id = $conn->insert_id;
                foreach ($cart as $item) {
                    $pid = $item['product_id'];
                    $qty = $item['quantity'];
                    $price = $item['price'];
                    $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $qty, $price)");
                    $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $pid");
                }
                $conn->query("DELETE FROM cart_items WHERE user_id = $user_id");
                $conn->commit();
                $success = 'Order placed successfully!';
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Checkout failed: ' . $e->getMessage();
            }
        } elseif (!$error) {
            $error = 'Cart is empty or invalid.';
        }
    }
}
$items = $conn->query("SELECT ci.*, p.name, p.price, p.image FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = $user_id");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
      .cart-table {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 24px 0 rgba(80, 80, 160, 0.08);
      }
      .cart-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #e0e7ff;
      }
      .sticky-summary {
        position: sticky;
        bottom: 0;
        background: #fff;
        box-shadow: 0 -2px 8px 0 rgba(80, 80, 160, 0.04);
        padding: 1rem 0;
        z-index: 10;
      }
      @media (max-width: 768px) {
        .sticky-summary { padding: 1rem; }
      }
      .empty-cart {
        text-align: center;
        margin-top: 3rem;
        color: #6366f1;
      }
      .empty-cart img {
        max-width: 180px;
        opacity: 0.7;
      }
      .qty-group { display: flex; align-items: center; gap: 0.5rem; }
      .qty-btn { width: 32px; height: 32px; border-radius: 50%; border: none; background: #e0e7ff; color: #3730a3; font-size: 1.2rem; }
      .qty-btn:active { background: #6366f1; color: #fff; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4" style="max-width:900px;">
        <h2 class="mb-4">My Cart</h2>
        <?php if ($success): ?>
            <div class="toast align-items-center text-bg-success border-0 show mb-3" role="alert" aria-live="assertive" aria-atomic="true" style="position:relative;z-index:9999;">
              <div class="d-flex">
                <div class="toast-body"><?= $success ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>
        <?php elseif ($error): ?>
            <div class="toast align-items-center text-bg-danger border-0 show mb-3" role="alert" aria-live="assertive" aria-atomic="true" style="position:relative;z-index:9999;">
              <div class="d-flex">
                <div class="toast-body"><?= $error ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>
        <?php endif; ?>
        <form method="post">
        <?php if ($items && $items->num_rows > 0): ?>
        <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle cart-table">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php $total=0; while($row = $items->fetch_assoc()): $line = $row['price'] * $row['quantity']; $total += $line; ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><img src="<?= $row['image'] ?: 'default-product.png' ?>" class="cart-img"></td>
                    <td>₹<?= number_format($row['price'], 2) ?></td>
                    <td>
                      <div class="qty-group">
                        <button type="button" class="qty-btn" onclick="changeQty(<?= $row['id'] ?>, -1)"><i class="bi bi-dash"></i></button>
                        <input type="number" name="qty[<?= $row['id'] ?>]" id="qty-<?= $row['id'] ?>" value="<?= $row['quantity'] ?>" min="1" class="form-control" style="width:60px;text-align:center;">
                        <button type="button" class="qty-btn" onclick="changeQty(<?= $row['id'] ?>, 1)"><i class="bi bi-plus"></i></button>
                      </div>
                    </td>
                    <td>₹<?= number_format($line, 2) ?></td>
                    <td>
                        <button name="remove" value="<?= $row['id'] ?>" class="btn btn-danger btn-sm">Remove</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th colspan="2">₹<?= number_format($total, 2) ?></th>
                </tr>
                <tr>
                  <td colspan="6" class="text-end">
                    <div class="input-group" style="max-width:300px;float:right;">
                      <input type="text" class="form-control" name="discount_code" placeholder="Discount code" autocomplete="off">
                      <button class="btn btn-outline-primary" type="button" disabled>Apply</button>
                    </div>
                  </td>
                </tr>
            </tfoot>
        </table>
        </div>
        <div class="sticky-summary d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-2 mb-md-0">
            <button name="update" class="btn btn-primary me-2">Update Cart</button>
            <button name="checkout" class="btn btn-success me-2">Checkout</button>
            <button name="remove_all" class="btn btn-outline-danger" onclick="return confirm('Remove all items from cart?');">Remove All</button>
            <a href="user_dashboard.php" class="btn btn-outline-secondary ms-2">Continue Shopping</a>
          </div>
          <div class="fw-bold fs-5">Total: ₹<?= number_format($total, 2) ?></div>
        </div>
        <?php else: ?>
        <div class="empty-cart">
          <img src="https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/shopping-cart.svg" alt="Empty cart">
          <h4 class="mt-3">Your cart is empty</h4>
          <p>Browse products and add them to your cart!</p>
          <a href="user_dashboard.php" class="btn btn-primary mt-2">Continue Shopping</a>
        </div>
        <?php endif; ?>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
function changeQty(id, delta) {
  var input = document.getElementById('qty-' + id);
  var val = parseInt(input.value) || 1;
  val += delta;
  if (val < 1) val = 1;
  input.value = val;
}
// Auto-hide toast
var toastElList = [].slice.call(document.querySelectorAll('.toast'));
toastElList.forEach(function(toastEl) {
  var toast = new bootstrap.Toast(toastEl, { delay: 2500 });
  toast.show();
});
</script>
</body>
</html> 