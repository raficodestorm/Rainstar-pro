<?php
require_once "../includes/config.php"; 
require_once "../includes/dbconnection.php"; 

if (!isset($_GET['return_id'])) {
    die("No return ID provided");
}
$return_id = intval($_GET['return_id']);

// Fetch return info (including customer, pharmacist)
$return = $conn->query("
    SELECT r.id, r.sale_id, r.quantity, r.unit_price, r.reason, r.pharmacist_id,
           s.customer_id, s.net_total AS original_net_total, s.paid_amount AS original_paid,
           c.name AS customer_name, u.username AS pharmacist_name,
           s.sale_date
    FROM sale_return_items r
    JOIN sales s ON s.id = r.sale_id
    JOIN customers c ON c.id = s.customer_id
    JOIN users u ON u.id = r.pharmacist_id
    WHERE r.id = $return_id
")->fetch_assoc();

if (!$return) die("Return record not found.");

// Fetch all items in this return
$items = $conn->query("SELECT stock_id, medicine, quantity, unit_price FROM sale_return_items WHERE id=$return_id");

// Calculate totals
$subtotal = 0;
while($row = $items->fetch_assoc()){
    $subtotal += $row['quantity'] * $row['unit_price'];
}
$items = $conn->query("SELECT stock_id, medicine, quantity, unit_price FROM sale_return_items WHERE id=$return_id");

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paid_amount = floatval($_POST['paid_amount']);
    $net_total = round($subtotal);  
    $due_amount = round($net_total - $paid_amount);
    $payment_method= $_POST['payment_method'];

    $status = ($due_amount <= 0) ? "Paid" : "Due";

    $stmt = $conn->prepare("UPDATE sale_return_items SET paid_amount=?, due_amount=?, status=?, payment_method=? WHERE id=?");
    $stmt->bind_param("ddssi", $paid_amount, $due_amount, $status, $payment_method, $return_id);
    $stmt->execute();
    $stmt->close();

    header("Location: sale_return_invoice.php?return_id=".$return_id);
    exit();
}

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<div class="container-fluid page-body-wrapper">
<?php include "../includes/navbar.php"; ?>
<div class="main-panel">
<div class="content-wrapper">

<div class="container">
<h2>💳 Sale Return Payment</h2>

<div class="summary">
    <p><strong>Return ID:</strong> SR<?= $return['id']; ?></p>
    <p><strong>Sale ID:</strong> BRP<?= $return['sale_id']; ?></p>
    <p><strong>Customer:</strong> <?= htmlspecialchars($return['customer_name']); ?></p>
    <p><strong>Subtotal Refund:</strong> ৳<?= number_format($subtotal,2); ?></p>
</div>

<table class="table-dark">
<tr><th>Medicine</th><th>Qty</th><th>Unit Price</th><th>Amount</th></tr>
<?php while($row = $items->fetch_assoc()): $total = $row['quantity'] * $row['unit_price']; ?>
<tr>
<td><?= htmlspecialchars($row['medicine']); ?></td>
<td class="right"><?= $row['quantity']; ?></td>
<td class="right"><?= number_format($row['unit_price'],2); ?></td>
<td class="right"><?= number_format($total,2); ?></td>
</tr>
<?php endwhile; ?>
</table>

<form method="post">
<label for="paid_amount">Paid Amount</label>
<input type="number" step="0.01" name="paid_amount" id="paid_amount" placeholder="Enter paid amount" value="<?= number_format($subtotal,2); ?>" required>

<label for="payment_method">Payment Method</label>
<select name="payment_method" id="payment_method" required>
    <option value="Cash">Cash</option>
    <option value="Card">Card</option>
    <option value="Mobile Banking">Mobile Banking</option>
</select>

<p><strong>Net Refund Total:</strong> ৳<span id="net_total"><?= number_format($subtotal,2); ?></span></p>

<button type="submit" class="submit_btn">Confirm Payment</button>
</form>
<div class="footer-note">RainStar Pharma - Secure Payment</div>
</div>

</div> <!-- content-wrapper -->
<?php include "../includes/footer.php"; ?>
</div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
