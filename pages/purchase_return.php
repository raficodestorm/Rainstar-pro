<?php
require_once "../includes/config.php"; 
require_once "../includes/dbconnection.php"; 
include "../includes/header.php";
include "../includes/sidebar.php";

$popup = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_no   = trim($_POST['invoice_number']);
    $supplier     = trim($_POST['supplier_name']);
    $reason       = trim($_POST['reason']);
    $total_refund = floatval($_POST['total_refund']);

    // Find purchase_id by invoice number
    $purchase_id = null;
    $stmt = $conn->prepare("SELECT id FROM purchases WHERE invoice_no = ?");
    $stmt->bind_param("s", $invoice_no);
    $stmt->execute();
    $stmt->bind_result($purchase_id);
    $stmt->fetch();
    $stmt->close();

    if ($purchase_id) {
        foreach ($_POST['medicine_name'] as $index => $medicine_name) {
            $qty        = intval($_POST['quantity'][$index]);
            $unit_price = floatval($_POST['unit_price'][$index]);

            // Find stock_id by medicine name
            $stock_id = null;
            $stmt = $conn->prepare("SELECT id FROM stock WHERE medicine_name = ? AND pharmacist_id = ?");
            $stmt->bind_param("si", $medicine_name, $pharmacist_id);
            $stmt->execute();
            $stmt->bind_result($stock_id);
            $stmt->fetch();
            $stmt->close();

            if ($stock_id) {
                // Insert into purchase_return
                $stmt = $conn->prepare("
                    INSERT INTO purchase_return (purchase_id, stock_id, medicine, quantity, reason, pharmacist_id) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iisisi", $purchase_id, $stock_id, $medicine_name, $qty, $reason, $pharmacist_id);
                $stmt->execute();
                $stmt->close();

                // Update stock
                $stm = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ? ");
                $stm->bind_param("ii", $qty, $stock_id);
                $stm->execute();
                $stm->close();
            }
        }
        $popup = true;
    } else {
        echo "<script>alert('❌ Invalid invoice number.');</script>";
    }
}
?>
<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">

      <div class="form-container">
        <h2>Purchase Return Form</h2>

        <form method="POST" id="purchaseReturn">
          <!-- Invoice -->
          <div class="form-group">
            <label for="invoice">Purchase Invoice Number</label>
            <input type="text" name="invoice_number" required placeholder="Enter purchase invoice number">
          </div>

          <!-- Supplier -->
          <div class="form-group">
            <label for="supplier">Supplier Name</label>
            <input list="supplier-list" name="supplier_name" required placeholder="Type supplier name">
            <datalist id="supplier-list">
              <?php
              $suppliers = $conn->query("SELECT name FROM supplier ORDER BY name ASC");
              while ($s = $suppliers->fetch_assoc()) {
                  echo "<option value='".htmlspecialchars($s['name'])."'>";
              }
              ?>
            </datalist>
          </div>

          <!-- Reason -->
          <div class="form-group">
            <label for="reason">Reason for Return</label>
            <textarea name="reason" rows="3" placeholder="Describe the reason for return" required></textarea>
          </div>

          <!-- Items Section -->
          <div class="section-title">Return Items</div>
          <div id="return-items-container"></div>
          <button type="button" class="add-btn" onclick="addReturnRow()">+ Add Item</button>

          <!-- Refund -->
          <div class="form-group" style="margin-top: 30px;">
            <label for="total-refund">Total Refund Amount</label>
            <input type="number" id="total-refund" name="total_refund" readonly value="0.00">
          </div>

          <!-- Submit -->
          <button type="submit" class="submit-btn" id="purchaseReturn">Confirm Purchase Return</button>
        </form>
      </div>
    </div>
  </div>
</div>

<audio id="click"><source src="../images/success.mp3" type="audio/mpeg"></audio>

<!-- ===================== JS ====================== -->
<script>
  // Medicine data from DB
  const medicines = [
    <?php
      $medicines = $conn->query("SELECT medicine_name, purchase_price FROM stock ORDER BY medicine_name ASC");
      $arr = [];
      while ($m = $medicines->fetch_assoc()) {
          $arr[] = "{name: '".addslashes($m['medicine_name'])."', price: {$m['purchase_price']}}";
      }
      echo implode(",", $arr);
    ?>
  ];

  function addReturnRow() {
    const container = document.getElementById('return-items-container');

    let options = medicines.map(med =>
      `<option value="${med.name}">`
    ).join('');

    const row = document.createElement('div');
    row.className = 'grid-row';
    row.innerHTML = `
      <div class="form-group">
        <label>Medicine</label>
        <input list="medicine-list" name="medicine_name[]" required oninput="setPrice(this)">
        <datalist id="medicine-list">${options}</datalist>
      </div>

      <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity[]" min="1" value="1" oninput="calculateRefund(this)">
      </div>

      <div class="form-group">
        <label>Unit Price</label>
        <input type="number" name="unit_price[]" step="0.01" value="0.00" readonly>
      </div>

      <div class="form-group">
        <label>Total</label>
        <input type="number" name="total[]" step="0.01" value="0.00" readonly>
      </div>

      <div class="form-group">
        <label>&nbsp;</label>
        <button type="button" class="delete-btn" onclick="removeRow(this)">❌</button>
      </div>
    `;
    container.appendChild(row);
  }

  function setPrice(input) {
    const row = input.closest('.grid-row');
    const med = medicines.find(m => m.name === input.value);
    if (med) {
      row.querySelector('[name="unit_price[]"]').value = med.price;
      calculateRefund(input);
    }
  }

  function calculateRefund(elem) {
    const row = elem.closest('.grid-row');
    const qty = parseInt(row.querySelector('[name="quantity[]"]').value) || 0;
    const price = parseFloat(row.querySelector('[name="unit_price[]"]').value) || 0;
    row.querySelector('[name="total[]"]').value = (qty * price).toFixed(2);
    updateRefundTotal();
  }

  function updateRefundTotal() {
    let total = 0;
    document.querySelectorAll('[name="total[]"]').forEach(input => {
      total += parseFloat(input.value) || 0;
    });
    document.getElementById('total-refund').value = total.toFixed(2);
  }

  function removeRow(button) {
    button.closest('.grid-row').remove();
    updateRefundTotal();
  }

  window.onload = () => addReturnRow();
</script>

  <script>
<?php if ($popup): ?>
  window.onload = function() {
    Swal.fire({
      title: '🏆 Successful!🏆',
      text: 'Your return has been saved successfully.',
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
      document.getElementById("purchaseReturn").reset();
    });
  };
<?php endif; ?>
</script>
<?php if (!empty($popup)) : ?>
<script>
    document.getElementById('click').play();
</script>
<?php endif; ?>
<style>
  .form-container {
    background: #191d24;
    border-radius: 14px;
    padding: 30px 40px;
    max-width: 900px;
    margin: auto;
    color: #fff;
  }
  h2 { text-align: center; margin-bottom: 25px; }
  .form-group { display: flex; flex-direction: column; margin-bottom: 20px; }
  label { margin-bottom: 6px; font-weight: 600; color: #ccc; }
  input, select, textarea {
    padding: 10px; border-radius: 8px; border: 1px solid #333;
    background: #2a2a2a; color: #fff;
  }
  input:focus, select:focus, textarea:focus {
    border-color: #4dabf7; outline: none;
  }
  .grid-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) 50px;
    gap: 20px; margin-bottom: 15px; align-items: end;
  }
  .add-btn, .submit-btn, .delete-btn {
    border: none; cursor: pointer; font-weight: 600; transition: 0.3s;
    border-radius: 8px; padding: 10px 15px;
  }
  .add-btn { background: #339af0; color: #fff; }
  .add-btn:hover { background: #74c0fc; }
  .submit-btn { width: 100%; background: #1c7ed6; color: #fff; padding: 12px; border-radius: 10px; }
  .submit-btn:hover { background: #4dabf7; }
  .delete-btn { background: #c0392b; color: #fff; }
  .delete-btn:hover { background: #e74c3c; }
</style>

<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
