<?php
session_start();
include "../includes/dbconnection.php";

// blank-page.php
// Keeps header, sidebar, navbar and footer. Content area is intentionally empty.
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">
<!-- contant area start----------------------------------------------------------------------------->
<?php
$popup = false;
if (!isset($_GET['purchase_id'])) {
    die("No purchase ID provided");
}
$purchase_id = intval($_GET['purchase_id']);

// Fetch sale info
$sale = $conn->query("
    SELECT p.id, p.total_amount, p.purchase_date, s.name AS supplier_name, u.username AS pharmacist_name
    FROM purchases p
    JOIN supplier s ON p.supplier_id = s.id
    JOIN users u ON p.pharmacist_id = u.id
    WHERE p.id = $purchase_id
")->fetch_assoc();

// Fetch sale items
$items = $conn->query("SELECT medicine, quantity, unit_price FROM purchase_items WHERE purchase_id = $purchase_id");

$subtotal = 0;
while($row = $items->fetch_assoc()){
    $subtotal += $row['quantity'] * $row['unit_price'];
}
$items = $conn->query("SELECT medicine, quantity, unit_price FROM purchase_items WHERE purchase_id = $purchase_id");

date_default_timezone_set('Asia/Dhaka');  
$date = date("Y-m-d H:i:s");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paid_amount   = floatval($_POST['paid_amount']);
    $total     = round($_POST['total']);  
    $due_amount    = round($net_total - $paid_amount);
    $payment_method= $_POST['payment_method'];

    $status = ($due_amount <= 0) ? "Paid" : "Due";

    // Now discount is VARCHAR, so use s instead of d in bind_param
    $stmt = $conn->prepare("UPDATE purchases SET paid_amount=?, due=?, status=?, payment_date=? WHERE id=?");
    $stmt->bind_param("dsssi", $paid_amount, $due_amount, $status, $date, $purchase_id);
    $stmt->execute();
    $stmt->close();
    $popup = true;
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment | RainStar Pharma</title>
<style>
.container {
    max-width: 750px;
    margin: auto;
    background: #191d24;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 25px rgba(0,0,0,0.5);
}
h2 { text-align: center; margin-bottom: 20px; color: #f7ede2; }
.summary {
    background: #212529;
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
    background: #212529; color: #f1f5f9; font-size: 15px;
}
select:focus, input:focus { outline: none; border-color: #38bdf8; }
.submit_btn {
    width: 100%; padding: 14px; font-size: 16px;
    background: linear-gradient(135deg, #06b6d4, #3b82f6);
    color: #fff; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s;
}
.submit_btn:hover { background: linear-gradient(135deg, #0ea5e9, #2563eb); }
.footer-note { text-align: center; margin-top: 15px; font-size: 12px; color: #64748b; }
.table-dark { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.table-dark th, .table-dark td {
    border: 1px solid #334155; padding: 8px; text-align: left;
}
.table-dark th { background-color: #1e293b; color: #38b000; }
.right { text-align: right; }
.discount{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
/* .dis{
    padding-right: 20px;
}
#paid_amount{
    width: 97%;
} */

</style>
<script>
function updateDue() {
    let subtotal = parseFloat(<?= $subtotal; ?>);
    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    let Grand_total = subtotal;
    let due = Math.round(Grand_total - paid);
    document.getElementById('net_total').innerText = Grand_total;
    document.getElementById('due_amount').innerText = due;
}
</script>
</head>
<body>
<div class="container">
<h2>💳 Payment</h2>

<div class="summary">
    <p><strong>Purchase ID:</strong> <?= $purchase['id']; ?></p>
    <p><strong>Supplier:</strong> <?= htmlspecialchars($purchase['purchase_name']); ?></p>
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

<form method="post" id="pay">

<label for="paid_amount">Paid Amount</label>
<input type="number" step="0.01" name="paid_amount" id="paid_amount" placeholder="Enter paid amount" oninput="updateDue()" required>

<label for="payment_method">Payment Method</label>
<select name="payment_method" id="payment_method" required>
    <option value="Cash">Cash</option>
    <option value="Card">Card</option>
    <option value="Mobile Banking">Mobile Banking</option>
</select>

<p><strong>Net Total:</strong> ৳<span id="net_total"><?= number_format($subtotal); ?></span></p>
<p><strong>Due Amount:</strong> ৳<span id="due_amount"><?= number_format($subtotal); ?></span></p>

<button type="submit" class="submit_btn">Confirm Payment</button>
</form>
<div class="footer-note">RainStar Pharma - Secure Payment</div>
</div>

<audio id="click">
  <source src="../images/success.mp3" type="audio/mpeg">
</audio>
<?php if (!empty($popup)) : ?>
<script>
    document.getElementById('click').play();
</script>
<?php endif; ?>

  <script>
<?php if ($popup): ?>
  window.onload = function() {
    Swal.fire({
      title: '🏆 Successful!🏆',
      text: 'Your payment has been completed successfully.',
      icon: 'success',
      background: 'linear-gradient(135deg,#3a86ff 0%,#db00b6 100%)', 
      color: '#fff',
      confirmButtonText: 'Great!',
      confirmButtonColor: '#072ac8',
      showClass: {
        popup: 'animate__animated animate__zoomIn'
      },
      hideClass: {
        popup: 'animate__animated animate__zoomOut'
      },
      customClass: {
        popup: 'rounded-3xl shadow-2xl p-6',
        title: 'text-3xl font-bold',
        confirmButton: 'px-6 py-2 rounded-full shadow-lg'
      },
      didOpen: () => {
        const duration = 2 * 1000; // 2 seconds
        const animationEnd = Date.now() + duration;
        (function frame() {
          confetti({
            particleCount: 5,
            startVelocity: 30,
            spread: 360,
            origin: { x: Math.random(), y: Math.random() - 0.2 }
          });
          if (Date.now() < animationEnd) {
            requestAnimationFrame(frame);
          }
        })();
      }
    }).then(() => {
      document.getElementById("pay").reset();
      window.location.href = "purchase_form.php";
    });
  };
<?php endif; ?>
</script>

</body>
</html>
<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
