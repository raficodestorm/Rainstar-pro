<?php
session_start();
include "../includes/dbconnection.php";

if (!isset($_GET['sale_id'])) {
    die("No sale ID provided");
}
$sale_id = intval($_GET['sale_id']);

// Fetch sale info
$sale = $conn->query("
    SELECT s.id, s.total_amount, s.sale_date, c.name AS customer_name, p.username AS pharmacist_name
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    JOIN users p ON s.pharmacist_id = p.id
    WHERE s.id = $sale_id
")->fetch_assoc();

// Fetch sale items
$items = $conn->query("SELECT medicine, quantity, unit_price FROM sale_items WHERE sale_id = $sale_id");

$subtotal = 0;
while($row = $items->fetch_assoc()){
    $subtotal += $row['quantity'] * $row['unit_price'];
}
$items = $conn->query("SELECT medicine, quantity, unit_price FROM sale_items WHERE sale_id = $sale_id");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $discount      = floatval($_POST['discount']);
    $paid_amount   = floatval($_POST['paid_amount']);
    $net_total     = $subtotal - $discount;
    $due_amount    = max(0, $net_total - $paid_amount);
    $payment_method= $_POST['payment_method'];

    // Payment status
    $status = ($due_amount <= 0) ? "Paid" : "Due";

    // Update sales table only
    $stmt = $conn->prepare("UPDATE sales SET discount=?, net_total=?, paid_amount=?, due=?, status=? WHERE id=?");
    $stmt->bind_param("dddssi", $discount, $net_total, $paid_amount, $due_amount, $status, $sale_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to invoice page
    header("Location: invoice.php?sale_id=" . $sale_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment | RainStar Pharma</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #0f172a;
    color: #f1f5f9;
    margin: 0; padding: 0;
}
.container {
    max-width: 750px;
    margin: 40px auto;
    background: #1e293b;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 25px rgba(0,0,0,0.5);
}
h2 { text-align: center; margin-bottom: 20px; color: #38bdf8; }
.summary {
    background: #0f172a;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid #334155;
}
.summary p { margin: 8px 0; font-size: 15px; }
label { display: block; margin-bottom: 8px; font-size: 14px; color: #cbd5e1; }
select, input {
    width: 100%; padding: 12px;
    border-radius: 10px; border: 1px solid #334155;
    margin-bottom: 20px;
    background: #0f172a; color: #f1f5f9; font-size: 15px;
}
select:focus, input:focus { outline: none; border-color: #38bdf8; }
button {
    width: 100%; padding: 14px; font-size: 16px;
    background: linear-gradient(135deg, #06b6d4, #3b82f6);
    color: #fff; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s;
}
button:hover { background: linear-gradient(135deg, #0ea5e9, #2563eb); }
.footer-note { text-align: center; margin-top: 15px; font-size: 12px; color: #64748b; }
.table-dark { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.table-dark th, .table-dark td {
    border: 1px solid #334155; padding: 8px; text-align: left;
}
.table-dark th { background-color: #1e293b; color: #38bdf8; }
.right { text-align: right; }
</style>
<script>
function updateDue() {
    let subtotal = parseFloat(<?= $subtotal; ?>);
    let discount = parseFloat(document.getElementById('discount').value) || 0;
    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    let net_total = subtotal - discount;
    let due = net_total - paid;
    document.getElementById('net_total').innerText = net_total.toFixed(2);
    document.getElementById('due_amount').innerText = due.toFixed(2);
}
</script>
</head>
<body>
<div class="container">
<h2>💳 Payment</h2>

<div class="summary">
    <p><strong>Sale ID:</strong> BRP<?= $sale['id']; ?></p>
    <p><strong>Customer:</strong> <?= htmlspecialchars($sale['customer_name']); ?></p>
    <p><strong>Subtotal:</strong> ৳<?= number_format($subtotal, 2); ?></p>
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
<label for="discount">Discount</label>
<input type="number" step="0.01" name="discount" id="discount" value="0" oninput="updateDue()">

<label for="paid_amount">Paid Amount</label>
<input type="number" step="0.01" name="paid_amount" id="paid_amount" placeholder="Enter paid amount" oninput="updateDue()" required>

<label for="payment_method">Payment Method</label>
<select name="payment_method" id="payment_method" required>
    <option value="Cash">Cash</option>
    <option value="Card">Card</option>
    <option value="Mobile Banking">Mobile Banking</option>
</select>

<p><strong>Net Total:</strong> ৳<span id="net_total"><?= number_format($subtotal, 2); ?></span></p>
<p><strong>Due Amount:</strong> ৳<span id="due_amount"><?= number_format($subtotal, 2); ?></span></p>

<button type="submit">Confirm Payment</button>
</form>
<div class="footer-note">RainStar Pharma - Secure Payment</div>
</div>
</body>
</html>
