<?php
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
 <!DOCTYPE html>
<html>
<head>
    <title>Stock</title>
    <style>
    
    table {
        width: 100%;
        border-collapse: collapse;
        background: #191d24;
        box-shadow: 0 0 15px rgba(0,0,0,0.5);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        padding: 12px;
        text-align: center;
        border: 1px solid #333;
    }

    th {
        background: #192bc2; /* Accent blue header */
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    td {
        background: #191d24;
        color: #dcdcdc;
    }
    .stc-title{
      text-align: center;
      color: #007bff;
      font-weight: bold;
    }
    tr:hover td{
      background-color: black;
      color: #ff4800;
    }
    tr:hover{
        border-left: 4px solid #ff4800;
    }

</style>

</head>
<body>

<div class="stc-title"><h2>Sale items record</h2></div>

<?php
$result = $conn->query("SELECT * FROM sale_items");
if ($result->num_rows > 0) {
    echo "<table><tr>
        <th>ID</th>
        <th>Sale id</th>
        <th>Stock id</th>
        <th>Medicine</th>
        <th>Quantity</th>
        <th>Unit Price</th>
    </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['sale_id']}</td>
            <td>{$row['stock_id']}</td>
            <td>{$row['medicine']}</td>
            <td>{$row['quantity']}</td>
            <td>{$row['unit_price']}</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found.</p>";
}
$conn->close();
?>
<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
