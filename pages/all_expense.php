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
<!-- contant area start----------------------------------------------------------------------------->
 <!DOCTYPE html>
<html>
<head>
  <title>Stock</title>
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

    /* Sticky header inside the scroll container */
    thead th{
      position: sticky;
      top: 0;                 /* adjust if you have fixed navbar */
      z-index: 2;
      background:#192bc2;
      color:#fff;
      text-transform:uppercase;
      letter-spacing:1px;
    }

    td{ background:#191d24; color:#dcdcdc; }

    .stc-title{ text-align:center; color:#007bff; font-weight:bold; }

    tr:hover td{ background:black; color:#9ef01a; }
    tr:hover{ border-left:4px solid #9ef01a; }
  </style>
</head>
<body>

<div class="stc-title"><h2>Expense record</h2></div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Amount</th>
        <th>Purpose</th>
        <th>Description</th>
        <th>date</th>
        
      </tr>
    </thead>
    <tbody>
      <?php
        $result = $conn->query("SELECT * FROM expense WHERE pharmacist_id = $pharmacist_id");
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
              <td>{$row['id']}</td>
              <td>{$row['amount']}</td>
              <td>{$row['purpose']}</td>
              <td>{$row['description']}</td>
              <td>{$row['spent_at']}</td>
              
            </tr>";
          }
        } else {
          echo '<tr><td colspan="9">No products found.</td></tr>';
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
