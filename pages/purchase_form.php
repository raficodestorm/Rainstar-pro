<?php
include "../includes/dbconnection.php";
 session_start();
$popup = false;
// Fetch medicines
$medicinesData = [];
$medicines = $conn->query("SELECT id AS stock_id, medicine_name, sale_price, quantity FROM stock");
while ($row = $medicines->fetch_assoc()) {
    $medicinesData[] = $row;
}

// Fetch medicine types
$typeData = [];
$medicineType = $conn->query("SELECT id, type_name FROM medicine_type");
while ($rows = $medicineType->fetch_assoc()) {
    $typeData[] = $rows;
}

// Fetch suppliers
$supplier = $conn->query("SELECT id, name FROM supplier");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        // Sanitize input
       $invoice_number   = trim($_POST['invoice_number']);
        $supplier_name    = trim($_POST['supplier_name']);
        $total_amount     = floatval($_POST['total_amount']);
        $pharmacist_id    = $_SESSION['id'];
        $pharmacist_name  = $_SESSION['username'];

        $supp_id = null;
            $supp_stmt = $conn->prepare("SELECT id FROM supplier WHERE name = ?");
            $supp_stmt->bind_param("s", $supplier_name);
            $supp_stmt->execute();
            $supp_stmt->bind_result($supp_id);
            $existss = $supp_stmt->fetch();
            $supp_stmt->close(); 

        // Insert purchase
        $purchase_stmt = $conn->prepare("
            INSERT INTO purchases (invoice_number,supplier_id, supplier_name, total_amount, pharmacist_name) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $purchase_stmt->bind_param("sisds", $invoice_number, $supp_id, $supplier_name, $total_amount, $pharmacist_name);
        $purchase_stmt->execute();
        $purchase_id = $purchase_stmt->insert_id;
        $purchase_stmt->close();

        // Loop items
        foreach ($_POST['medicine_name'] as $i => $medicine_name) {
            $type_name   = $_POST['type_name'][$i];
            $expiry_date = $_POST['expiry_date'][$i];
            $quantity    = intval($_POST['quantity'][$i]);
            $unit_price  = floatval($_POST['unit_price'][$i]);
            $sale_price  = floatval($_POST['sale_price'][$i]);

            // Ensure medicine type
            $medicine_type_id = null;
            $type_stmt = $conn->prepare("SELECT id FROM medicine_type WHERE type_name = ?");
            $type_stmt->bind_param("s", $type_name);
            $type_stmt->execute();
            $type_stmt->bind_result($medicine_type_id);
            $exists = $type_stmt->fetch();
            $type_stmt->close();  

            if (!$exists) {
                $insert_type = $conn->prepare("INSERT INTO medicine_type (type_name) VALUES (?)");
                $insert_type->bind_param("s", $type_name);
                $insert_type->execute();
                $medicine_type_id = $insert_type->insert_id;
                $insert_type->close();
                }

            // Check stock
            $stock_id = null;
            $current_qty = 0;
            $stock_stmt = $conn->prepare("SELECT id, quantity FROM stock WHERE medicine_name = ?");
            $stock_stmt->bind_param("s", $medicine_name);
            $stock_stmt->execute();
            $stock_stmt->bind_result($stock_id, $current_qty);
            if ($stock_stmt->fetch()) {
                $new_qty = $current_qty + $quantity;
                $stock_stmt->close();
                $update_stock = $conn->prepare("
                    UPDATE stock 
                    SET quantity = ?, purchase_price = ?, sale_price = ?, expiry_date = ?
                    WHERE id = ?
                ");
                $update_stock->bind_param("iddsi", $new_qty, $unit_price, $sale_price, $expiry_date, $stock_id);
                $update_stock->execute();
                $update_stock->close();
            } else {
                $stock_stmt->close();
                $insert_stock = $conn->prepare("
                    INSERT INTO stock (medicine_name, medicine_type_id, quantity, purchase_price, sale_price, expiry_date, supplier_name)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $insert_stock->bind_param("siiddsi", $medicine_name, $medicine_type_id, $quantity, $unit_price, $sale_price, $expiry_date, $supplier_name);
                $insert_stock->execute();
                $stock_id = $insert_stock->insert_id;
                $insert_stock->close();
            }

            // Insert purchase item
            $item_stmt = $conn->prepare("
                INSERT INTO purchase_items (purchase_id, stock_id, quantity, unit_price) 
                VALUES (?, ?, ?, ?)
            ");
            $item_stmt->bind_param("iiid", $purchase_id, $stock_id, $quantity, $unit_price);
            $item_stmt->execute();
            $item_stmt->close();
        }

        $conn->commit();
        $popup = true;

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>
<?php
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">
<!------------------------------------- contant area start------------------------------------->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchase Form</title>
  <style>
        
    .form-container {
      background: #1a1f2e;
      border-radius: 14px;
      padding: 30px 20px;
      max-width: 1000px;
      width: 100%;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.05);
      box-sizing: border-box;
      margin: auto;
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #ffffff;
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
    }

    input, select {
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #333;
      font-size: 15px;
      background-color: rgba(40, 40, 40, 0.9);
      color: #ffffff;
      transition: all 0.3s ease;
      width: 100%; /* full width inside container */
      box-sizing: border-box;
    }

    input:focus, select:focus {
      border-color: #4dabf7;
      outline: none;
      box-shadow: 0 0 8px rgba(77, 171, 247, 0.5);
      background-color: rgba(50, 50, 50, 0.95);
    }

    .section-title {
      margin: 20px 0 10px;
      font-size: 18px;
      color: #bbbbbb;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 5px;
    }

    /* Header for return items */
    #purchase-items-header {
      display: grid;
      grid-template-columns: 1.5fr 1.5fr 1.8fr 1fr 1fr 1fr 1fr 50px;
      gap: 20px;
      margin-bottom: 8px;
      font-weight: 600;
      color: #ccc;
      user-select: none;
      align-items: center;
    }
    #purchase-items-header div{
      text-align: center;
    }

    /* Container for all return item rows */
    #purchase-items-container {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 10px;
    }

    /* Each return item row is a grid with 6 columns */
    .purchase-item-row {
      display: grid;
      grid-template-columns: 1.5fr 1.5fr 1.5fr 1fr 1fr 1fr 1fr 50px;
      gap: 15px;
      align-items: center;
    }

    /* Remove margin bottom inside form-groups of return items */
    #purchase-items-container .form-group {
      margin-bottom: 0;
    }

    .add-medicine-btn {
      margin: 10px 0 30px;
      padding: 10px 18px;
      border: none;
      background: linear-gradient(135deg, #4dabf7, #339af0);
      color: white;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-block;
    }

    .add-medicine-btn:hover {
      background: linear-gradient(135deg, #74c0fc, #4dabf7);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(77, 171, 247, 0.4);
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

    .delete-btn {
      padding: 8px 10px;
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s ease;
      width: 40px;
      height: 40px;
      line-height: 1;
    }

    .delete-btn:hover {
      background: linear-gradient(135deg, #ff6b6b, #d63031);
      transform: scale(1.1);
    }

    /* Existing styles above */

/* Responsive adjustments */
@media (max-width: 720px) {
  #purchase-items-header,
  .purchase-item-row {
    grid-template-columns: 1fr; /* single column layout */
    gap: 15px;
  }

  /* Label and input stack vertically */
  .purchase-item-row .form-group {
    width: 100%;
  }

  /* Align labels properly */
  #purchase-items-header > div {
    display: none; /* hide the header grid labels on mobile, optional */
  }
}

@media (max-width: 480px) {
  input, select, textarea {
    font-size: 16px;
    padding: 14px;
  }

  .add-btn, .submit-btn {
    width: 100%;
    font-size: 18px;
    padding: 14px 0;
  }

  .delete-btn {
    width: 48px;
    height: 48px;
  }
}

    
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Purchase Entry</h2>
    <form id="purchaseForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
      <div class="form-group">
        <label for="invoice">Purchase Invoice Number</label>
        <input type="text" name="invoice_number" id="invoice" required placeholder="Enter purchase invoice number" />
      </div>

      <div class="form-group">
        <label for="supplier">Select Supplier</label>
        <input type="text" list="supplierlist" name="supplier_name" required placeholder="Type or select">
        <datalist id="supplierlist">
          <?php while($row = $supplier->fetch_assoc()): ?>
            <option value="<?php echo htmlspecialchars($row['name']); ?>"></option>
          <?php endwhile; ?>         
        </datalist>
      </div>

      <div class="section-title">Purchase Items</div>

      <div id="purchase-items-header">
        <div>Medicine</div>
        <div>Generic Name</div>
        <div>Expiry Date</div>
        <div>Quantity</div>
        <div>Unit Price</div>
        <div>Sale Price</div>
        <div>Total</div>
        <div>&nbsp;</div>
      </div>

      <div id="purchase-items-container"></div>

      <button type="button" class="add-medicine-btn">+ Add Item</button>

      <div class="form-group" style="margin-top: 30px;">
        <label for="total-amount">Total Amount</label>
        <input type="number" id="total-amount" name="total_amount" readonly value="0.00" />
      </div>

      <button type="submit" class="submit-btn">Confirm Purchase</button>
    </form>
  </div>
  <audio id="click">
  <source src="../images/success.mp3" type="audio/mpeg">
  </audio>
  <datalist id="medicineList">
    <?php foreach($medicinesData as $row): ?>
        <option value="<?= htmlspecialchars($row['medicine_name']); ?>" 
                data-id="<?= $row['stock_id']; ?>" 
                data-qty="<?= $row['quantity']; ?>" 
                data-price="<?= $row['sale_price']; ?>">
        </option>
    <?php endforeach; ?>
  </datalist>

  <datalist id="typeList">
    <?php foreach($typeData as $rows): ?>
        <option value="<?= htmlspecialchars($rows['type_name']); ?>" data-id="<?= $rows['id']; ?>"></option>
    <?php endforeach; ?>
  </datalist>

<?php if (!empty($popup)): ?>
<script>
    document.getElementById('click').play();
</script>
<?php endif; ?>

  <script>
<?php if ($popup): ?>
  window.onload = function() {
    Swal.fire({
      title: '🏆 Successful! 🏆',
      text: 'Your purchase has been saved successfully.',
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
      document.getElementById("purchaseForm").reset();
    });
  };
<?php endif; ?>
</script>
  <script>
    const medicines = <?= json_encode($medicinesData); ?>;
    class PurchaseForm {
        constructor(formId, containerId, grandTotalId, medicines) {
            this.form = document.getElementById(formId);
            this.container = document.getElementById(containerId);
            this.grandTotal = document.getElementById(grandTotalId);
            this.medicines = medicines;
            this.init();
        }

        init() {
            this.addRow();
            document.querySelector('.add-medicine-btn').addEventListener('click', () => this.addRow());

            this.form.addEventListener('submit', (e) => {
                if (!confirm('Confirm this purchase?')) e.preventDefault();
            });
        }

        addRow() {
            const row = document.createElement('div');
            row.className = 'purchase-item-row';
            row.innerHTML = `
                <div class="form-group">
                    <input list="medicineList" name="medicine_name[]" required placeholder="Select medicine">
                </div>
                <div class="form-group">
                    <input list="typeList" name="type_name[]" required placeholder="Select or type">
                </div>
                <div class="form-group">
                    <input type="date" name="expiry_date[]" required />
                </div>
                <div class="form-group">
                    <input type="number" name="quantity[]" min="1" value="1" required />
                </div>
                <div class="form-group">
                    <input type="number" name="unit_price[]" step="0.01" placeholder="Enter unit price" required />
                </div>
                <div class="form-group">
                    <input type="number" name="sale_price[]" step="0.01" placeholder="Enter sale price" required />
                </div>
                <div class="form-group">
                    <input type="number" name="total[]" step="0.01" value="0.00" readonly />
                </div>
                <div class="form-group">
                    <button type="button" class="delete-btn">❌</button>
                </div>
            `;

            const qtyInput = row.querySelector('[name="quantity[]"]');
            const unitInput = row.querySelector('[name="unit_price[]"]');
            const totalInput = row.querySelector('[name="total[]"]');

            const updateRow = () => {
                const qty = parseFloat(qtyInput.value) || 0;
                const unit = parseFloat(unitInput.value) || 0;
                totalInput.value = (qty * unit).toFixed(2);  
                this.updateTotal();
            };

            qtyInput.addEventListener('input', updateRow);
            unitInput.addEventListener('input', updateRow);

            row.querySelector('.delete-btn').addEventListener('click', () => {
                row.remove();
                this.updateTotal();
            });

            this.container.appendChild(row);
        }

        updateTotal() {
            let total = 0;
            this.container.querySelectorAll('[name="total[]"]').forEach(inp => {
                total += parseFloat(inp.value) || 0;
            });
            this.grandTotal.value = total.toFixed(2);
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        new PurchaseForm('purchaseForm', 'purchase-items-container', 'total-amount', medicines);
    });
  </script>
</body>
</html>

<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
