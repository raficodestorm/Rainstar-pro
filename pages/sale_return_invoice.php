<?php
require_once "../includes/config.php"; 
require_once "../includes/dbconnection.php"; 

if (!isset($_GET['return_id'])) {
    die("No sale return ID provided");
}

$return_id = intval($_GET['return_id']);

// Fetch return info with related sale and customer
$return = $conn->query("
    SELECT sri.id AS return_id, sri.sale_id, sri.reason, sri.pharmacist_id, sri.unit_price, sri.medicine AS medicine, sri.quantity,
           s.sale_date, c.name AS customer_name, p.username AS pharmacist_name
    FROM sale_return_items sri
    JOIN sales s ON sri.sale_id = s.id
    JOIN customers c ON s.customer_id = c.id
    JOIN users p ON sri.pharmacist_id = p.id
    WHERE sri.pharmacist_id = $pharmacist_id
    AND sri.id = $return_id
")->fetch_all(MYSQLI_ASSOC);

if (!$return) {
    die("Sale return not found.");
}

// Aggregate total refund
$total_refund = 0;
$medicine = null;
foreach($return as $row) {
    $total_refund += $row['quantity'] * $row['unit_price'];
    $medicine =  $row['medicine'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Return Invoice #<?= $return[0]['return_id']; ?></title>
<style>
body {
    font-family: 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.4;
    color: #000;
}
.rain{ font-size: 20px; }
.center { text-align: center; }
.bold { font-weight: bold; }
.line { border-top: 1px dashed #000; margin: 5px 0; }
table { width: 100%; border-collapse: collapse; }
td { padding: 2px 0; }
.right { text-align: right; }
.left { text-align: left; }
.status-refund { color: green; font-weight: bold; }
.btn { display: flex; justify-content: space-between; margin-top: 10px; }
@media print { .btn { display: none; } }
</style>
</head>
<body>

<div class="center bold rain">RainStar Pharma</div>
<div class="center">Lalbag, Dhaka</div>
<br>
<div class="center bold">SALES RETURN RECEIPT</div>
<br>

Return ID: BRP<?= $return[0]['return_id']; ?><br>
Original Sale ID: BRP<?= $return[0]['sale_id']; ?><br>
Customer: <?= htmlspecialchars($return[0]['customer_name']); ?><br>
Transaction by: <?= htmlspecialchars($return[0]['pharmacist_name']); ?><br>
<table style="width:100%;">
    <tr>
        <td>Date: <?= date("d-m-Y", strtotime($return[0]['sale_date'])); ?></td>
        <td class="right">Time: <?= date("h:i A", strtotime($return[0]['sale_date'])); ?></td>
    </tr>
</table>

<div class="line"></div>
<table>
<tr class="bold">
    <td>SL</td>
    <td>Description</td>
    <td class="right">Qty</td>
    <td class="right">Unit Price</td>
    <td class="right">Amt</td>
</tr>
<?php
$sl = 1;
foreach($return as $row):
    $total = $row['quantity'] * $row['unit_price'];
?>
<tr>
    <td><?= $sl++; ?></td>
    <td><?= htmlspecialchars($medicine); ?></td>
    <td class="right"><?= $row['quantity']; ?></td>
    <td class="right"><?= number_format($row['unit_price'],2); ?></td>
    <td class="right"><?= number_format($total,2); ?></td>
</tr>
<?php endforeach; ?>
</table>
<div class="line"></div>

<div class="bold">Total Refund &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($total_refund, 2); ?></div>
<br>
Reason: <?= htmlspecialchars($return[0]['reason']); ?><br>

<div class="line"></div>
<div style="font-size: 12px;">
    ক্যাশমেমো ছাড়া বিক্রিত ঔষধ ফেরত এবং বিক্রির ১৫ দিন পর ঔষধ ফেরত বা পরিবর্তনকালে টাকা দেওয়া হয়না।<br>
    ফ্রিজের আইটেম ও কাটা পাতার ঔষধ ফেরত বা পরিবর্তন করা হয় না।
</div>
<div class="line"></div>
<div class="center">---------Software Developed by--------- <br> 
                    ----------S A Rafi 01877100096---------</div>
<br>
<div class="btn">
    <button class="reset-btn" onclick="window.location.href = 'sale_return_form.php'">➕ New Return</button>
    <button class="print-btn" onclick="window.print()">🖨 Print Invoice</button>
</div>

</body>
</html>
