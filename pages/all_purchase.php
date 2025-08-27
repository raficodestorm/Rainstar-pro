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
  <title>purchase Record</title>
  <style>
    body { margin:0; padding:0; background:#111; }

    /* Scroll container */
    .table-wrap{
      max-height: 70vh;        /* how tall you want the table area */
      overflow: auto;          /* scrolling happens here */
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0,0,0,0.5);
      background: #191d24;
    }

    table{
      width:100%;
      border-collapse: separate; /* sticky-friendly */
      border-spacing: 0;
    }

    th, td{
      padding:12px;
      text-align:center;
      border:1px solid #333;
    }

    /* Sticky header */
    thead th{
      position: sticky;
      top: 0;
      z-index: 2;
      background:#192bc2;
      color:#fff;
      text-transform:uppercase;
      letter-spacing:1px;
    }

    td{ background:#191d24; color:#dcdcdc; }

    .stc-title{ text-align:center; color:#007bff; font-weight:bold; }

    tr:hover td{ background:black; color:#9ef01a; }
    tr:hover { border-left:4px solid #9ef01a !important; }
    .status-btn {
      padding: 5px 12px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: bold;
      text-transform: uppercase;
      border: none;
      cursor: default;
    }

    .status-paid {
      background: #007bff; /* blue */
      color: #fff;
    }

    .status-due {
      background: #dc3545; /* red */
      color: #fff;
    }
  </style>
</head>
<body>

<div class="stc-title"><h2>Purchase Record</h2></div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Invoice No</th>
        <th>Supplier id</th>
        <th>Supplier name</th>
        <th>Total</th>
        <th>Paid</th>
        <th>Due</th>
        <th>Status</th>
        <th>Pharmacist</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $result = $conn->query("SELECT * FROM purchases");
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $statusClass = ($row['status'] === 'Paid') ? 'status-paid' : 'status-due';
            $statusText  = ($row['status'] === 'Paid') ? 'Paid' : 'Due';
            echo "<tr>
              <td>{$row['id']}</td>
              <td>{$row['invoice_no']}</td>
              <td>{$row['supplier_id']}</td>
              <td>{$row['supplier_name']}</td>
              <td>{$row['total_amount']}</td>
              <td>{$row['paid_amount']}</td>
              <td>{$row['due']}</td>
              <td><span class='status-btn {$statusClass}'>{$statusText}</span></td>
              <td>{$row['pharmacist_name']}</td>
              <td>{$row['purchase_date']}</td>
            </tr>";
          }
        } else {
          echo '<tr><td colspan="5">No records found.</td></tr>';
        }
        $conn->close();
      ?>
    </tbody>
  </table>
</div>

</body>
</html>

<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
