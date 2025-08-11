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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchase Form</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

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
      max-width: 900px;
      margin: auto;
      border: 1px solid rgba(255, 255, 255, 0.05);
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
    }

    input:focus, select:focus {
      border-color: #4dabf7;
      outline: none;
      box-shadow: 0 0 8px rgba(77, 171, 247, 0.5);
      background-color: rgba(50, 50, 50, 0.95);
    }

    .grid-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) 50px;
      gap: 20px;
      margin-bottom: 15px;
      align-items: end;
    }

    .add-btn {
      margin-top: 10px;
      padding: 10px 18px;
      border: none;
      background: linear-gradient(135deg, #4dabf7, #339af0);
      color: white;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .add-btn:hover {
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

    .section-title {
      margin: 20px 0 10px;
      font-size: 18px;
      color: #bbbbbb;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 5px;
    }

    .delete-btn {
      padding: 8px 10px;
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .delete-btn:hover {
      background: linear-gradient(135deg, #ff6b6b, #d63031);
      transform: scale(1.05);
    }

    @media (max-width: 600px) {
      .grid-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Purchase Form</h2>
    <form id="purchaseForm">
      <div class="form-group">
        <label for="invoice">Purchase Invoice Number</label>
        <input type="text" name="invoice_number" required placeholder="Enter purchase invoice number">
      </div>

      <div class="form-group">
        <label for="supplier">Select Supplier</label>
        <select name="supplier_id" required>
          <option value="" disabled selected hidden>Select a supplier</option>
          <option value="1">ABC Pharma</option>
          <option value="2">XYZ Distributors</option>
        </select>
      </div>

      <div class="form-group">
        <label for="purchase-date">Purchase Date</label>
        <input type="date" id="purchase-date" name="purchase_date" required>
      </div>

      <div class="section-title">Purchased Items</div>

      <div id="purchase-items-container"></div>

      <button type="button" class="add-btn" onclick="addPurchaseRow()">+ Add Item</button>

      <div class="form-group" style="margin-top: 30px;">
        <label for="total-purchase">Total Purchase Amount</label>
        <input type="number" id="total-purchase" name="total_purchase" readonly value="0.00">
      </div>

      <button type="submit" class="submit-btn">Confirm Purchase</button>
    </form>
  </div>

  <script>
    function addPurchaseRow() {
      const container = document.getElementById('purchase-items-container');

      const row = document.createElement('div');
      row.className = 'grid-row';
      row.innerHTML = `
        <div class="form-group">
          <label>Medicine</label>
          <select name="stock_id[]" required onchange="updateFromSelection(this)">
            <option value="1" data-price="8">NAPA (Supplier Price)</option>
            <option value="2" data-price="10">Maxpro (Supplier Price)</option>
          </select>
        </div>

        <div class="form-group">
          <label>Quantity</label>
          <input type="number" name="quantity[]" min="1" value="1" oninput="recalculateRow(this)">
        </div>

        <div class="form-group">
          <label>Unit Price</label>
          <input type="number" name="unit_price[]" step="0.01" value="8.00" oninput="recalculateRow(this)">
        </div>

        <div class="form-group">
          <label>Total</label>
          <input type="number" name="total[]" step="0.01" value="8.00" readonly>
        </div>

        <div class="form-group">
          <label>&nbsp;</label>
          <button type="button" class="delete-btn" onclick="removeRow(this)">❌</button>
        </div>
      `;

      container.appendChild(row);
      updatePurchaseTotal();
    }

    function updateFromSelection(select) {
      const row = select.closest('.grid-row');
      const price = select.options[select.selectedIndex].dataset.price;
      row.querySelector('[name="unit_price[]"]').value = price;
      recalculateRow(select);
    }

    function recalculateRow(elem) {
      const row = elem.closest('.grid-row');
      const qty = parseFloat(row.querySelector('[name="quantity[]"]').value) || 0;
      const price = parseFloat(row.querySelector('[name="unit_price[]"]').value) || 0;
      row.querySelector('[name="total[]"]').value = (qty * price).toFixed(2);
      updatePurchaseTotal();
    }

    function updatePurchaseTotal() {
      let total = 0;
      document.querySelectorAll('[name="total[]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
      });
      document.getElementById('total-purchase').value = total.toFixed(2);
    }

    function removeRow(button) {
      button.closest('.grid-row').remove();
      updatePurchaseTotal();
    }

    window.onload = () => {
      // Auto-set date to today
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('purchase-date').value = today;
      addPurchaseRow();
    }
  </script>
</body>
</html>
<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
