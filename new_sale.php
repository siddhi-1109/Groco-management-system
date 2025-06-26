<?php
include 'protect.php';
include 'db.php';
$role = $_SESSION['role'] ?? '';
$success = '';
$error = '';
$products = $conn->query("SELECT id, name, price, stock FROM products");
$product_list = [];
while ($row = $products->fetch_assoc()) {
    $product_list[] = $row;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = $_POST['items'] ?? [];
    $customer_name = trim($_POST['customer_name'] ?? '');
    $discount = floatval($_POST['discount'] ?? 0);
    $tax = floatval($_POST['tax'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $total = 0;
    $stock_ok = true;
    // Check stock
    foreach ($items as $item) {
        $pid = intval($item['product_id']);
        $qty = intval($item['quantity']);
        $prod = $conn->query("SELECT stock FROM products WHERE id=$pid")->fetch_assoc();
        if (!$prod || $prod['stock'] < $qty) {
            $stock_ok = false;
            $error = "Insufficient stock for product ID $pid.";
            break;
        }
        $total += $item['price'] * $qty;
    }
    $total = $total - $discount + $tax;
    if ($stock_ok && $items && $total >= 0) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO sales (user_id, customer_name, total, discount, tax) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isd dd", $user_id, $customer_name, $total, $discount, $tax);
            $stmt->execute();
            $sale_id = $conn->insert_id;
            foreach ($items as $item) {
                $pid = intval($item['product_id']);
                $qty = intval($item['quantity']);
                $price = floatval($item['price']);
                $stmt2 = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("iiid", $sale_id, $pid, $qty, $price);
                $stmt2->execute();
                // Update stock
                $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $pid");
            }
            $conn->commit();
            $success = "Sale created! <a href='invoice.php?id=$sale_id' target='_blank'>View Invoice</a>";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to save sale: " . $e->getMessage();
        }
    } elseif (!$error) {
        $error = "Invalid sale data.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    let products = <?= json_encode($product_list) ?>;
    function updatePriceAndStock(row) {
        let select = row.querySelector('.product-select');
        let priceInput = row.querySelector('.product-price');
        let stockSpan = row.querySelector('.product-stock');
        let selected = products.find(p => p.id == select.value);
        if (selected) {
            priceInput.value = selected.price;
            stockSpan.textContent = 'Stock: ' + selected.stock;
        } else {
            priceInput.value = '';
            stockSpan.textContent = '';
        }
        updateTotal();
    }
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.sale-row').forEach(row => {
            let price = parseFloat(row.querySelector('.product-price').value) || 0;
            let qty = parseInt(row.querySelector('.product-qty').value) || 0;
            total += price * qty;
        });
        let discount = parseFloat(document.getElementById('discount').value) || 0;
        let tax = parseFloat(document.getElementById('tax').value) || 0;
        total = total - discount + tax;
        document.getElementById('sale-total').textContent = '₹' + total.toFixed(2);
    }
    function addRow() {
        let table = document.getElementById('sale-items');
        let row = document.createElement('tr');
        row.className = 'sale-row';
        row.innerHTML = `
            <td>
                <select name="items[][product_id]" class="form-select product-select" required onchange="updatePriceAndStock(this.closest('tr'))">
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                </select>
                <span class="text-muted small product-stock"></span>
            </td>
            <td><input type="number" name="items[][quantity]" class="form-control product-qty" min="1" value="1" required onchange="updateTotal()"></td>
            <td><input type="number" name="items[][price]" class="form-control product-price" min="0" step="0.01" required readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); updateTotal();">Remove</button></td>
        `;
        table.appendChild(row);
    }
    window.addEventListener('DOMContentLoaded', function() {
        addRow();
    });
    </script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'navbar.php'; ?>
    <div class="container py-4" style="max-width:900px;">
        <h2 class="mb-4">New Sale</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post" onsubmit="return confirm('Confirm sale?');">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Optional">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount</label>
                    <input type="number" name="discount" id="discount" class="form-control" min="0" step="0.01" value="0" onchange="updateTotal()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tax</label>
                    <input type="number" name="tax" id="tax" class="form-control" min="0" step="0.01" value="0" onchange="updateTotal()">
                </div>
            </div>
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="sale-items">
                </tbody>
            </table>
            <button type="button" class="btn btn-outline-primary mb-3" onclick="addRow()">Add Product</button>
            <div class="mb-3">
                <strong>Total: <span id="sale-total">₹0.00</span></strong>
            </div>
            <button class="btn btn-success" type="submit">Create Sale</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('product-select')) {
        updatePriceAndStock(e.target.closest('tr'));
    }
    if (e.target.classList.contains('product-qty') || e.target.id === 'discount' || e.target.id === 'tax') {
        updateTotal();
    }
});
</script>
</body>
</html> 