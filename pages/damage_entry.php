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
   <?php
if (isset($_GET['damage_item'])) {
    $medicine = $_GET['damage_items'];

    $sql = $conn->prepare("SELECT id, purchase_price, quantity FROM stock WHERE medicine_name=? LIMIT 1");
    $sql->bind_param("s", $medicine);
    $sql->execute();
    $result = $sql->get_result()->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($result);
    exit; // 🔴 very important so the rest of HTML does not echo
}

$popup = false; // default no popup

if (isset($_POST['damage'])) {
    $medicine = $_POST['damage_item'];
    $quantity = intval($_POST['quantity']);
    $unit_price = floatval($_POST['unit_price']);
    $description = $_POST['description'];
    $pharmacist_id = $_SESSION['user_id']; // assuming login system

    // get stock id & available qty
    $stock = $conn->prepare("SELECT id, quantity FROM stock WHERE medicine_name=? LIMIT 1");
    $stock->bind_param("s", $medicine);
    $stock->execute();
    $stockData = $stock->get_result()->fetch_assoc();

    if ($stockData && $stockData['quantity'] >= $quantity) {
        $stock_id = $stockData['id'];

        // insert into damage
        $insert = $conn->prepare("INSERT INTO damage(stock_id, medicine, quantity, unit_price, pharmacist_id) VALUES(?, ?, ?, ?, ?)");
        $insert->bind_param("isidi", $stock_id, $medicine, $quantity, $unit_price, $pharmacist_id);

        if ($insert->execute()) {
            // deduct from stock
            $update = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id=?");
            $update->bind_param("ii", $quantity, $stock_id);
            $update->execute();

            $popup = true;
        } else {
            echo "<div style='color:red;'>Error: " . $insert->error . "</div>";
        }
    } else {
        echo "<div style='color:red;'>Error: Not enough stock available!</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Expense</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
      color: #e0e0e0;
    }

    .form-container {
      background: #12151e;
      backdrop-filter: blur(12px);
      border-radius: 14px;
      padding: 30px 40px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
      max-width: 700px;
      margin: 20px auto;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #ffffff;
      font-size: 24px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 20px;
    }

    label {
      margin-bottom: 8px;
      font-weight: 600;
      color: #bdbdbd;
      font-size: 14px;
    }

    input, textarea {
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #333;
      font-size: 15px;
      background-color: rgba(40, 40, 40, 0.9);
      color: #ffffff;
      transition: all 0.3s ease;
    }

    input:focus, textarea:focus {
      border-color: #4dabf7;
      outline: none;
      box-shadow: 0 0 8px rgba(77, 171, 247, 0.5);
      background-color: rgba(50, 50, 50, 0.95);
    }

    .submit-btn {
      background: linear-gradient(135deg, #4dabf7, #1c7ed6);
      color: white;
      padding: 12px 20px;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      width: 100%;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .submit-btn:hover {
      background: linear-gradient(135deg, #74c0fc, #4dabf7);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(77, 171, 247, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 600px) {
      .form-container {
        padding: 10px;
        margin: auto;
      }

      h2 {
        font-size: 20px;
      }

      label {
        font-size: 13px;
      }

      input, textarea {
        font-size: 14px;
        padding: 8px 12px;
      }

      .submit-btn {
        font-size: 15px;
        padding: 10px 16px;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Damage Entry</h2>
    <form id="damageForm" method="POST">
  <div class="form-group">
    <label for="damage_item">Damage item:</label>
    <input type="text" list="damage_list" id="damage_item" name="damage_item" required placeholder="Enter product name">
    <datalist id="damage_list">
        <?php
        $medicine_name = $conn->query("SELECT id, medicine_name FROM stock ORDER BY medicine_name ASC");
        while ($s = $medicine_name->fetch_assoc()) {
            echo "<option value='".htmlspecialchars($s['medicine_name'])."' data-id='".$s['id']."'>";
        }
        ?>
    </datalist>
  </div>

  <div class="form-group">
    <label for="quantity">Quantity:</label>
    <input type="number" id="quantity" name="quantity" required placeholder="Enter quantity">
  </div>

  <div class="form-group">
    <label for="unit_price">Unit Price:</label>
    <input type="text" id="unit_price" name="unit_price" readonly>
  </div>

  <div class="form-group">
    <label for="description">Description:</label>
    <input type="text" id="description" name="description" placeholder="description">
  </div>

  <button type="submit" class="submit-btn" name="damage">Submit</button>
</form>
  </div>

  <audio id="click">
  <source src="../images/success.mp3" type="audio/mpeg">
</audio>

<script>
document.getElementById("damage_item").addEventListener("change", function() {
    let medicine = this.value;
    if (medicine) {
        fetch("?medicine=" + encodeURIComponent(medicine))
            .then(res => res.json())
            .then(data => {
                if (data) {
                    document.getElementById("unit_price").value = data.purchase_price;
                    document.getElementById("quantity").setAttribute("max", data.quantity);
                }
            });
    }
});
</script>

  <script>
<?php if ($popup): ?>
  window.onload = function() {
    Swal.fire({
      title: '🏆 Successful!🏆',
      text: 'Your expense has been saved successfully.',
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
      document.getElementById("expense").reset();
    });
  };
<?php endif; ?>
</script>
<?php if (!empty($popup)) : ?>
<script>
    document.getElementById('click').play();
</script>
<?php endif; ?>
</body>

</html>
<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
