<?php
session_start();
include "../includes/dbconnection.php";

if (!isset($_GET['sale_id'])) {
    die("No sale ID provided");
}
$sale_id = intval($_GET['sale_id']);

// Fetch sale info (now including discount, net_total, paid_amount, due, status)
$sale = $conn->query("
    SELECT s.id, s.total_amount, s.discount, s.net_total, s.paid_amount, s.due, s.status, s.sale_date, 
           c.name AS customer_name, p.username AS pharmacist_name
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    JOIN users p ON s.pharmacist_id = p.id
    WHERE s.id = $sale_id
")->fetch_assoc();

// Fetch sale items
$items = $conn->query("SELECT medicine, quantity, unit_price FROM sale_items WHERE sale_id = $sale_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #<?= $sale['id']; ?></title>
<style>
    body {
        font-family: 'Courier New', monospace; /* thermal printer look */
        font-size: 14px;
        line-height: 1.4;
        color: #000;
    }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .line { border-top: 1px dashed #000; margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 2px 0; }
    .right { text-align: right; }
    .left { text-align: left; }
    .status-paid { color: green; font-weight: bold; }
    .status-due { color: red; font-weight: bold; }
    @media print {
        .print-btn { display: none; }
    }
</style>
</head>
<body>

<div class="center bold">RainStar Pharma</div>
<div class="center">Lalbag, Dhaka</div>
<br>
<div class="center bold">SALES RECEIPT</div>
<br>

Invoice ID: BRP<?= $sale['id']; ?><br>
Customer: <?= htmlspecialchars($sale['customer_name']); ?><br>
Pharmacist: <?= htmlspecialchars($sale['pharmacist_name']); ?><br>
<table style="width:100%;">
    <tr>
        <td>Date: <?= date("d-m-Y", strtotime($sale['sale_date'])); ?></td>
        <td class="right">Time: <?= date("h:i A", strtotime($sale['sale_date'])); ?></td>
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
    $subtotal = 0;
    while($row = $items->fetch_assoc()):
        $total = $row['quantity'] * $row['unit_price'];
        $subtotal += $total;
    ?>
    <tr>
        <td><?= $sl++; ?></td>
        <td><?= htmlspecialchars($row['medicine']); ?></td>
        <td class="right"><?= $row['quantity']; ?></td>
        <td class="right"><?= number_format($row['unit_price'], 2); ?></td>
        <td class="right"><?= number_format($total, 2); ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<div class="line"></div>

<table>
    <tr><td class="left">Subtotal</td><td class="right"><?= number_format($subtotal, 2); ?></td></tr>
    <tr><td class="left">Discount</td><td class="right"><?= htmlspecialchars($sale['discount'], 2); ?></td></tr>
    <tr><td class="left">VAT</td><td class="right">0.00</td></tr>
    <tr><td class="left">Rounding</td><td class="right">0.00</td></tr>
</table>
<div class="line"></div>

<div class="bold">Net Total &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($sale['net_total'], 2); ?></div>
<br>
Payment Info: <?= ($sale['status'] == "Paid") ? '<span class="status-paid">PAID</span>' : '<span class="status-due">DUE</span>'; ?><br>
Amount Paid: <?= number_format($sale['paid_amount'], 2); ?><br>
Due Amount: <?= number_format($sale['due'], 2); ?><br>

<div class="line"></div>
<div style="font-size: 12px;">
    ক্যাশমেমো ছাড়া বিক্রিত ঔষধ ফেরত এবং বিক্রির ১৫ দিন পর ঔষধ ফেরত বা পরিবর্তনকালে টাকা দেওয়া হয়না।<br>
    ফ্রিজের আইটেম ও কাটা পাতার ঔষধ ফেরত বা পরিবর্তন করা হয় না।
</div>
<div class="line"></div>
<div class="center">---------Software Developed by--------- <br> 
                    ----------S A Rafi 01877100096---------
</div>

<br>
<button class="print-btn" onclick="window.print()">🖨 Print Receipt</button>

</body>
</html>
