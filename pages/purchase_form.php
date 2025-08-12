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
    
    .form-container {
      background: #1a1f2e;
      border-radius: 14px;
      padding: 30px 40px;
      max-width: 900px;
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
      grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr 50px;
      gap: 20px;
      margin-bottom: 5px;
      font-weight: 600;
      color: #ccc;
      user-select: none;
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
      grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr 50px;
      gap: 20px;
      align-items: center;
    }

    /* Remove margin bottom inside form-groups of return items */
    #purchase-items-container .form-group {
      margin-bottom: 0;
    }

    .add-btn {
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
    <form id="purchaseReturnForm">
      <div class="form-group">
        <label for="invoice">Purchase Invoice Number</label>
        <input type="text" name="invoice_number" id="invoice" required placeholder="Enter purchase invoice number" />
      </div>

      <div class="form-group">
        <label for="supplier">Select Supplier</label>
        <select name="supplier_id" id="supplier" required>
          <option value="" disabled selected hidden>Select a supplier</option>
          <option value="1">ABC Pharma</option>
          <option value="2">XYZ Distributors</option>
        </select>
      </div>

      <div class="section-title">Purchase Items</div>

      <div id="purchase-items-header">
        <div>Medicine</div>
        <div>Expiry Date</div>
        <div>Quantity</div>
        <div>Unit Price</div>
        <div>Total</div>
        <div>&nbsp;</div>
      </div>

      <div id="purchase-items-container">
        <!-- purchase item rows go here -->
      </div>

      <button type="button" class="add-btn" onclick="addReturnRow()">+ Add Item</button>

      <div class="form-group" style="margin-top: 30px;">
        <label for="total-refund">Total Amount</label>
        <input type="number" id="total-amount" name="total_refund" readonly value="0.00" />
      </div>

      <button type="submit" class="submit-btn">Confirm Purchase</button>
    </form>
  </div>

  <script>
    function addReturnRow() {
      const container = document.getElementById('purchase-items-container');

      const row = document.createElement('div');
      row.className = 'purchase-item-row';

      row.innerHTML = `
        <div class="form-group">
          <select name="stock_id[]" required onchange="calculateRefund(this)">
            <option value="1" data-price="8">NAPA (Supplier Price)</option>
            <option value="2" data-price="10">Maxpro (Supplier Price)</option>
          </select>
        </div>
        <div class="form-group">
          <input type="date" name="expiry_date[]" required />
        </div>
        <div class="form-group">
          <input type="number" name="quantity[]" min="1" value="1" oninput="calculateRefund(this)" required />
        </div>
        <div class="form-group">
          <input type="number" name="unit_price[]" step="0.01" value="8.00" readonly />
        </div>
        <div class="form-group">
          <input type="number" name="total[]" step="0.01" value="8.00" readonly />
        </div>
        <div class="form-group">
          <button type="button" class="delete-btn" onclick="removeRow(this)">❌</button>
        </div>
      `;

      container.appendChild(row);
      updateRefundTotal();
    }

    function calculateRefund(elem) {
      const row = elem.closest('.purchase-item-row');

      const qtyInput = row.querySelector('input[name="quantity[]"]');
      const qty = parseFloat(qtyInput.value) || 0;

      const select = row.querySelector('select[name="stock_id[]"]');
      const price = parseFloat(select.options[select.selectedIndex].dataset.price) || 0;

      const unitPriceInput = row.querySelector('input[name="unit_price[]"]');
      const totalInput = row.querySelector('input[name="total[]"]');

      unitPriceInput.value = price.toFixed(2);
      totalInput.value = (qty * price).toFixed(2);

      updateRefundTotal();
    }

    function updateRefundTotal() {
      let total = 0;
      document.querySelectorAll('input[name="total[]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
      });
      document.getElementById('total-amount').value = total.toFixed(2);
    }

    function removeRow(button) {
      const row = button.closest('.purchase-item-row');
      if (row) {
        row.remove();
        updateRefundTotal();
      }
    }

    window.onload = () => {
      addReturnRow();
    };
  </script>
</body>
</html>

<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
