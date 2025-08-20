<?php
session_start();
include "../includes/dbconnection.php";

if (!isset($_GET['sale_id'])) {
    die("No sale ID provided");
}
$sale_id = intval($_GET['sale_id']);

// Fetch sale info
$sale = $conn->query("
    SELECT s.id, s.total_amount, s.sale_date, 
           c.name AS customer_name, 
           p.username AS pharmacist_name
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    JOIN pharmacist p ON s.pharmacist_id = p.id
    WHERE s.id = $sale_id
")->fetch_assoc();

// Fetch sale items
$items = $conn->query("SELECT medicine, quantity, unit_price FROM sale_items WHERE sale_id = $sale_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice #<?= $sale['id']; ?></title>
<style>
body { 
    font-family: Arial, sans-serif; 
}
.invoice-box {
     max-width: 800px; 
     margin: auto; 
     padding: 30px; 
     border: 1px solid #eee; 
    }
h2 { 
    text-align: center; 
}
table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-top: 20px; 
}
table, th, td { 
    border: 1px solid #ccc; 
    padding: 8px; 
    text-align: left; 
}
tfoot td { 
    font-weight: bold; 
}
.print-btn { 
    margin: 20px auto; 
    display: block; 
    padding: 10px 20px; 
    background: #28a745; 
    color: white; 
    border: none; 
    cursor: pointer; 
}
img{
    width: 60px;
    height: 60px;
    text-align: center;
    margin-left: 360px;
}
</style>
</head>
<body>
<div class="invoice-box">
    <img src="../images/rainstar.png" alt="">
    <h2>Invoice</h2>
    <p><strong>Invoice ID:</strong> <?= $sale['id']; ?></p>
    <p><strong>Customer:</strong> <?= htmlspecialchars($sale['customer_name']); ?></p>
    <p><strong>Pharmacist:</strong> <?= htmlspecialchars($sale['pharmacist_name']); ?></p>
    <p><strong>Date:</strong> <?= $sale['sale_date']; ?></p>

    <table>
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['medicine']); ?></td>
                    <td><?= $row['quantity']; ?></td>
                    <td><?= number_format($row['unit_price'], 2); ?></td>
                    <td><?= number_format($row['quantity'] * $row['unit_price'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Grand Total</td>
                <td><?= number_format($sale['total_amount'], 2); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<button class="print-btn" onclick="window.print()">🖨 Print Invoice</button>
</body>
</html>
