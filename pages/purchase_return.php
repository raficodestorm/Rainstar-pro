<?php
require_once "../includes/config.php"; 
require_once "../includes/dbconnection.php"; 
include "../includes/header.php";
include "../includes/sidebar.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_no   = $_POST['invoice_number'];
    $supplier_id  = $_POST['supplier_id'];
    $reason       = $_POST['reason'];
    $total_refund = $_POST['total_refund'];

    // Find purchase_id by invoice number
    $purchase_id = null;
    $stmt = $conn->prepare("SELECT id FROM purchases WHERE invoice_no = ?");
    $stmt->bind_param("s", $invoice_no);
    $stmt->execute();
    $stmt->bind_result($purchase_id);
    $stmt->fetch();
    $stmt->close();

    if ($purchase_id) {
        foreach ($_POST['stock_id'] as $index => $stock_id) {
            $qty        = intval($_POST['quantity'][$index]);
            $unit_price = floatval($_POST['unit_price'][$index]);

            // Insert into purchase_return
            $stmt = $conn->prepare("
                INSERT INTO purchase_return (purchase_id, stock_id, quantity, reason) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiis", $purchase_id, $stock_id, $qty, $reason);
            $stmt->execute();
            $stmt->close();

            // Update stock (reduce returned quantity)
            $conn->query("UPDATE stock SET quantity = quantity - $qty WHERE id = $stock_id");
        }

        echo "<script>
                alert('✅ Purchase return recorded successfully!');
                window.location.href='purchase_return.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('❌ Invalid invoice number.');</script>";
    }
}
?>
<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">
<!-- contant area start----------------------------------------------------------------------------->
   
<div class="form-container">
        <h2>Purchase Return Form</h2>

        <form method="POST">
          <!-- Invoice -->
          <div class="form-group">
            <label for="invoice">Purchase Invoice Number</label>
            <input type="text" name="invoice_number" required placeholder="Enter purchase invoice number">
          </div>

          <!-- Supplier -->
          <div class="form-group">
            <label for="supplier">Select Supplier</label>
            <select name="supplier_id" required>
              <option value="" disabled selected hidden>Select a supplier</option>
              <?php
              $suppliers = $conn->query("SELECT id, name FROM supplier ORDER BY name ASC");
              while ($s = $suppliers->fetch_assoc()) {
                  echo "<option value='{$s['id']}'>{$s['name']}</option>";
              }
              ?>
            </select>
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
          <button type="submit" class="submit-btn">Confirm Purchase Return</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===================== JS ====================== -->
<script>
  // Medicine data from DB
  const medicines = [
    <?php
      $medicines = $conn->query("SELECT id, medicine_name, purchase_price FROM stock ORDER BY medicine_name ASC");
      $arr = [];
      while ($m = $medicines->fetch_assoc()) {
          $arr[] = "{id: {$m['id']}, name: '".addslashes($m['medicine_name'])."', price: {$m['purchase_price']}}";
      }
      echo implode(",", $arr);
    ?>
  ];

  // Add new row
  function addReturnRow() {
    const container = document.getElementById('return-items-container');

    let options = medicines.map(med =>
      `<option value="${med.id}" data-price="${med.price}">${med.name}</option>`
    ).join('');

    const row = document.createElement('div');
    row.className = 'grid-row';
    row.innerHTML = `
      <div class="form-group">
        <label>Medicine</label>
        <select name="stock_id[]" required onchange="calculateRefund(this)">
          <option value="" disabled selected>Select medicine</option>
          ${options}
        </select>
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

  // Calculate refund for a row
  function calculateRefund(elem) {
    const row = elem.closest('.grid-row');
    const qty = parseInt(row.querySelector('[name="quantity[]"]').value) || 0;
    const select = row.querySelector('[name="stock_id[]"]');
    const price = select.options[select.selectedIndex]?.dataset.price || 0;

    row.querySelector('[name="unit_price[]"]').value = price;
    row.querySelector('[name="total[]"]').value = (qty * price).toFixed(2);

    updateRefundTotal();
  }

  // Update total refund
  function updateRefundTotal() {
    let total = 0;
    document.querySelectorAll('[name="total[]"]').forEach(input => {
      total += parseFloat(input.value) || 0;
    });
    document.getElementById('total-refund').value = total.toFixed(2);
  }

  // Remove row
  function removeRow(button) {
    button.closest('.grid-row').remove();
    updateRefundTotal();
  }

  // Add first row on page load
  window.onload = () => addReturnRow();
</script>
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
