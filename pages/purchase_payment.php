<?php
require_once "../includes/config.php"; 
require_once "../includes/dbconnection.php"; 

include "../includes/header.php";
include "../includes/sidebar.php";
?>
<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">
<!-- content area start -------------------------------------------------------->
<?php
$popup = false;

if (!isset($_GET['purchase_id'])) {
    die("No purchase ID provided");
}
$purchase_id = intval($_GET['purchase_id']);

// Fetch purchase info
$purchase = $conn->query("SELECT supplier_name, total_amount FROM purchases WHERE id = $purchase_id")->fetch_assoc();

// Fetch purchase items
$items = [];
$subtotal = 0;
$result = $conn->query("SELECT medicine, quantity, unit_price FROM purchase_items WHERE purchase_id = $purchase_id");
while ($row = $result->fetch_assoc()) {
    $row['total'] = $row['quantity'] * $row['unit_price'];
    $subtotal += $row['total'];
    $items[] = $row;
}
$result->free();

date_default_timezone_set('Asia/Dhaka');  
$date = date("Y-m-d H:i:s");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paid_amount    = floatval($_POST['paid_amount']);
    $net_total      = floatval($_POST['net_total']);  
    $due_amount     = round($net_total - $paid_amount, 2);
    $payment_method = $_POST['payment_method'];

    $status = ($due_amount <= 0) ? "Paid" : "Due";

    $stmt = $conn->prepare("UPDATE purchases 
                            SET paid_amount=?, due=?, status=?, payment_date=? 
                            WHERE id=?");
    $stmt->bind_param("dsssi", $paid_amount, $due_amount, $status, $date, $purchase_id);
    $stmt->execute();
    $stmt->close();

    $popup = true;
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
.discount {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
</style>
<script>
function updateDue() {
    let subtotal = parseFloat(<?= $subtotal; ?>);
    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    let grand_total = subtotal;
    let due = Math.round(grand_total - paid);

    document.getElementById('net_total').innerText = grand_total.toFixed(2);
    document.getElementById('due_amount').innerText = due.toFixed(2);
    document.getElementById('net_total_input').value = grand_total; // ✅ hidden input
}
</script>
</head>
<body>
<div class="container">
<h2>💳 Payment</h2>

<div class="summary">
    <p><strong>Purchase ID:</strong> <?= $purchase_id; ?></p>
    <p><strong>Supplier:</strong> <?= htmlspecialchars($purchase['supplier_name']); ?></p>
    <p><strong>Subtotal:</strong> ৳<?= number_format($subtotal, 2); ?></p>
</div>

<table class="table-dark">
<tr><th>Medicine</th><th>Qty</th><th>Unit Price</th><th>Amount</th></tr>
<?php foreach ($items as $row): ?>
<tr>
<td><?= htmlspecialchars($row['medicine']); ?></td>
<td class="right"><?= $row['quantity']; ?></td>
<td class="right"><?= number_format($row['unit_price'],2); ?></td>
<td class="right"><?= number_format($row['total'],2); ?></td>
</tr>
<?php endforeach; ?>
</table>

<form method="post" id="pay">
    <label for="paid_amount">Paid Amount</label>
    <input type="number" step="0.01" name="paid_amount" id="paid_amount" 
           placeholder="Enter paid amount" oninput="updateDue()" required>

    <label for="payment_method">Payment Method</label>
    <select name="payment_method" id="payment_method" required>
        <option value="Cash">Cash</option>
        <option value="Card">Card</option>
        <option value="Mobile Banking">Mobile Banking</option>
    </select>

    <!-- ✅ hidden input for net_total -->
    <input type="hidden" name="net_total" id="net_total_input" value="<?= $subtotal; ?>">

    <p><strong>Net Total:</strong> ৳<span id="net_total"><?= number_format($subtotal, 2); ?></span></p>
    <p><strong>Due Amount:</strong> ৳<span id="due_amount"><?= number_format($subtotal, 2); ?></span></p>

    <button type="submit" class="submit_btn">Confirm Payment</button>
</form>

<div class="footer-note">RainStar Pharma - Secure Payment</div>
</div>

<audio id="click">
  <source src="../images/success.mp3" type="audio/mpeg">
</audio>

<?php if ($popup): ?>
<script>
  document.getElementById('click').play();
  window.onload = function() {
    Swal.fire({
      title: '🏆 Successful!🏆',
      text: 'Your payment has been completed successfully.',
      icon: 'success',
      background: 'linear-gradient(135deg,#3a86ff 0%,#db00b6 100%)', 
      color: '#fff',
      confirmButtonText: 'Great!',
      confirmButtonColor: '#072ac8',
      showClass: { popup: 'animate__animated animate__zoomIn' },
      hideClass: { popup: 'animate__animated animate__zoomOut' },
      customClass: {
        popup: 'rounded-3xl shadow-2xl p-6',
        title: 'text-3xl font-bold',
        confirmButton: 'px-6 py-2 rounded-full shadow-lg'
      },
      didOpen: () => {
        const duration = 2000; 
        const animationEnd = Date.now() + duration;
        (function frame() {
          confetti({
            particleCount: 5,
            startVelocity: 30,
            spread: 360,
            origin: { x: Math.random(), y: Math.random() - 0.2 }
          });
          if (Date.now() < animationEnd) requestAnimationFrame(frame);
        })();
      }
    }).then(() => {
      document.getElementById("pay").reset();
      window.location.href = "purchase_form.php";
    });
  };
</script>
<?php endif; ?>
</body>
</html>
<!-- content area end --------------------------------------------------------->
    </div> <!-- content-wrapper ends -->
    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->

<!-- $purchase = $conn->query("
    SELECT p.id, p.total_amount, p.purchase_date, s.name AS supplier_name, u.username AS pharmacist_name
    FROM purchases p
    JOIN supplier s ON p.supplier_id = s.id
    JOIN users u ON p.pharmacist_name = u.username
    WHERE p.id = $purchase_id
")->fetch_assoc(); -->