<?php
require_once "../includes/config.php"; 
require_once "../includes/dbconnection.php";
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_GET['action'] === 'sale_lookup') {
        $sale_id = intval($_GET['sale_id'] ?? 0);
        if ($sale_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Invalid sale id']);
            exit;
        }

        $sql = "
            SELECT s.id, s.customer_id, s.sale_date, s.pharmacist_id,
                   c.name AS customer_name
            FROM sales s
            LEFT JOIN customers c ON c.id = s.customer_id
            WHERE s.id = ?
            AND s.pharmacist_id = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $sale_id, $pharmacist_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $sale = $res->fetch_assoc();
        $stmt->close();

        if (!$sale) {
            echo json_encode(['ok' => false, 'error' => 'Sale not found']);
            exit;
        }

        echo json_encode(['ok' => true, 'sale' => $sale]);
        exit;
    }

    if ($_GET['action'] === 'sale_items') {
        $sale_id = intval($_GET['sale_id'] ?? 0);
        if ($sale_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Invalid sale id']);
            exit;
        }

        // Items actually sold in this sale, with current purchase_price for profit calc
        $sql = "
            SELECT si.stock_id AS stock_id,
                   COALESCE(si.medicine, st.medicine_name) AS medicine,
                   si.unit_price AS sale_unit_price,
                   COALESCE(st.purchase_price, 0) AS purchase_price,
                   si.quantity AS sold_qty
            FROM sale_items si
            LEFT JOIN stock st ON st.id = si.stock_id
            WHERE si.sale_id = ?
            AND si.pharmacist_id = ?
            ORDER BY medicine ASC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $sale_id, $pharmacist_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $ress = $stmt->get_result()->fetch_assoc();
        $stk_id = $ress['stock_id'];
        $items = [];
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
        
        $stmt->close();
        
        echo json_encode(['ok' => true, 'items' => $items]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    exit;
}

/**
 * -------------------------
 * FORM SUBMISSION (POST)
 * -------------------------
 * Inserts into return_items, updates stock, sale_items, sales totals,
 * and subtracts profit from revenue.
 */
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect inputs
    $sale_id      = intval($_POST['invoice_number'] ?? 0); // treating invoice as sale_id
    $customer_id  = intval($_POST['customer_id'] ?? 0);
    $reason       = trim($_POST['reason'] ?? '');
    $stock_ids    = $stk_id;
    $medicine     = htmlspecialchars($_POST['medicine']);
    $qtys         = $_POST['quantity'] ?? [];
    $unit_prices  = $_POST['unit_price'] ?? []; // sale unit prices from UI

    if ($sale_id <= 0 || $customer_id <= 0 || $reason === '' || empty($stock_ids)) {
        $flash = ['type' => 'error', 'msg' => 'Please fill all required fields.'];
    } else {
        // Validate sale exists and matches customer
        $stmt = $conn->prepare("SELECT id, customer_id, sale_date, pharmacist_id, total_amount, net_total, paid_amount, due FROM sales WHERE id=?");
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $sale_res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$sale_res) {
            $flash = ['type' => 'error', 'msg' => 'Sale not found.'];
        } elseif (intval($sale_res['customer_id']) !== $customer_id) {
            $flash = ['type' => 'error', 'msg' => 'Selected customer does not match this sale.'];
        } else {
            // Begin transaction
            $conn->begin_transaction();
            try {
                $sale_date      = substr($sale_res['sale_date'], 0, 10); // YYYY-MM-DD
                $pharmacist_id  = (string)$sale_res['pharmacist_id'];
                $total_refund   = 0.0;
                $profit_deduction = 0.0;

                // Prepared statements
                $ins_return = $conn->prepare("
                    INSERT INTO sale_return_items (sale_id, stock_id, medicine, quantity, unit_price, reason, pharmacist_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $upd_stock = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE id = ?");
                $sel_si    = $conn->prepare("SELECT quantity, unit_price FROM sale_items WHERE sale_id=? AND stock_id=?");
                $upd_si    = $conn->prepare("UPDATE sale_items SET quantity = quantity - ? WHERE sale_id=? AND stock_id=?");
                $del_si    = $conn->prepare("DELETE FROM sale_items WHERE sale_id=? AND stock_id=?");

                // For profit: need purchase_price from stock
                $sel_stock_price = $conn->prepare("SELECT purchase_price FROM stock WHERE id=?");

                foreach ($stock_ids as $i => $stock_id_raw) {
                    $stock_id   = intval($stock_id_raw);
                    $qty        = max(0, intval($qtys[$i] ?? 0));
                    $sale_price = floatval($unit_prices[$i] ?? 0);

                    if ($stock_id <= 0 || $qty <= 0 || $sale_price < 0) {
                        continue; // skip invalid rows
                    }

                    // Ensure not returning more than sold for this item
                    $sel_si->bind_param("ii", $sale_id, $stock_id);
                    $sel_si->execute();
                    $si_row = $sel_si->get_result()->fetch_assoc();
                    if (!$si_row) {
                        throw new Exception("Item (stock_id=$stock_id) not found in this sale.");
                    }
                    $sold_qty_for_item = intval($si_row['quantity']);
                    if ($qty > $sold_qty_for_item) {
                        throw new Exception("Return quantity ($qty) exceeds sold quantity ($sold_qty_for_item).");
                    }

                    // Insert into return_items (store sale unit price)
                    $ins_return->bind_param("iiidsi", $sale_id, $stock_id, $medicine, $qty, $sale_price, $reason, $pharmacist_id);
                    $ins_return->execute();
                    $return_id = $ins_return->insert_id;
                    $ins_return->close();

                    // Add quantity back to stock
                    $upd_stock->bind_param("ii", $qty, $stock_id);
                    $upd_stock->execute();

                    // Reduce sale_items qty or delete if zero
                    if ($qty === $sold_qty_for_item) {
                        $del_si->bind_param("ii", $sale_id, $stock_id);
                        $del_si->execute();
                    } else {
                        $upd_si->bind_param("iii", $qty, $sale_id, $stock_id);
                        $upd_si->execute();
                    }

                    // Refund and profit delta
                    $line_refund = $qty * $sale_price;
                    $total_refund += $line_refund;

                    // Purchase price (for profit deduction)
                    $sel_stock_price->bind_param("i", $stock_id);
                    $sel_stock_price->execute();
                    $pp_row = $sel_stock_price->get_result()->fetch_assoc();
                    $purchase_price = floatval($pp_row['purchase_price'] ?? 0);
                    $profit_deduction += $qty * ($sale_price - $purchase_price);
                }

                // Update sales totals (subtract totals safely)
                $new_total_amount = max(0, floatval($sale_res['total_amount']) - $total_refund);
                $new_net_total    = max(0, floatval($sale_res['net_total']) - $total_refund);
                $paid_amount      = floatval($sale_res['paid_amount']);
                $due              = floatval($sale_res['due']);

                // Recalculate due / paid to keep invariants: paid <= net_total, due = net_total - paid
                if ($paid_amount > $new_net_total) {
                    $paid_amount = $new_net_total;
                }
                $due = max(0, $new_net_total - $paid_amount);

                $upd_sales = $conn->prepare("
                    UPDATE sales
                    SET total_amount = ?, net_total = ?, paid_amount = ?, due = ?
                    WHERE id = ?
                ");
                $upd_sales->bind_param("dddii", $new_total_amount, $new_net_total, $paid_amount, $due, $sale_id);
                $upd_sales->execute();

                $profit_delta_int = (int)round($profit_deduction);

                // If there is any profit to deduct, update that day's revenue row
                if ($profit_delta_int !== 0) {
                    // Try update existing row
                    $upd_rev = $conn->prepare("UPDATE revenue SET amount = amount - ? WHERE pharmacist_id = ? AND `Date` = ?");
                    $upd_rev->bind_param("iss", $profit_delta_int, $pharmacist_id, $sale_date);
                    $upd_rev->execute();

                    if ($conn->affected_rows === 0) {
                        // Insert a new row with negative amount
                        $ins_rev = $conn->prepare("INSERT INTO revenue (pharmacist_id, amount, `Date`) VALUES (?, ?, ?)");
                        $neg = -$profit_delta_int;
                        // We insert negative because we are subtracting
                        $ins_rev->bind_param("sis", $pharmacist_id, $neg, $sale_date);
                        $ins_rev->execute();
                        $ins_rev->close();
                    }
                    $upd_rev->close();
                }

                // Commit
                $conn->commit();
                header("Location: sale_return_invoice.php?return_id=" . $return_id);
            } catch (Throwable $e) {
                $conn->rollback();
                $flash = ['type' => 'error', 'msg' => 'Failed to record return: ' . $e->getMessage()];
            }
        }
    }
} 
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">
<!-- contant area start----------------------------------------------------------------------------->
  <style>
    /* Reuse the same dark theme styles from your sales form */
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

    .sreturn-form-container {
      background: #191d24;
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

    .sreturn-form-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 20px;
    }

    label {
      margin-bottom: 8px;
      font-weight: 600;
      color: #bdbdbd;
    }

    input, select, textarea {
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #333;
      font-size: 15px;
      background-color: rgba(40, 40, 40, 0.9);
      color: #ffffff;
      transition: all 0.3s ease;
    }

    input:focus, select:focus, textarea:focus {
      border-color: #4dabf7;
      outline: none;
      box-shadow: 0 0 8px rgba(77, 171, 247, 0.5);
      background-color: rgba(50, 50, 50, 0.95);
    }

    .sreturn-grid-row {
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
  background: linear-gradient(135deg, #27ae60, #1e8449);
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.add-btn:hover {
  background: linear-gradient(135deg, #2ecc71, #239b56);
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
    .sreturn-section-title {
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
      .sreturn-grid-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
<?php if ($flash): ?>
        <div style="max-width:900px;margin:0 auto 16px auto;padding:12px;border-radius:8px;
                    background:<?= $flash['type']==='success' ? '#143d2a' : '#3d1414' ?>;
                    color:#fff;border:1px solid rgba(255,255,255,.08)">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <div class="sreturn-form-container">
        <h2>Sales Return Entry</h2>
        <form id="salesReturnForm" method="POST" autocomplete="off">
          <div class="sreturn-form-group">
            <label for="invoice">Sale ID (Invoice)</label>
            <input type="number" name="invoice_number" id="invoice_number" required placeholder="Enter sale id">
            <small id="sale_meta" style="margin-top:6px;opacity:.85"></small>
          </div>

          <div class="sreturn-form-group">
            <label for="customer">Customer</label>
            <select name="customer_id" id="customer_id" required>
              <option value="" disabled selected hidden>Select a customer</option>
              <?php
              $cs = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
              while ($c = $cs->fetch_assoc()) {
                  echo "<option value='{$c['id']}'>".htmlspecialchars($c['name'])."</option>";
              }
              ?>
            </select>
          </div>

          <div class="sreturn-form-group">
            <label for="reason">Reason for Return</label>
            <textarea name="reason" rows="3" placeholder="Describe the reason for return" required></textarea>
          </div>

          <div class="sreturn-section-title">Return Items</div>
          <div id="return-items-container"></div>
          <button type="button" class="add-btn" onclick="addReturnRow()">+ Add Item</button>

          <div class="sreturn-form-group" style="margin-top: 30px;">
            <label for="total-refund">Total Refund Amount</label>
            <input type="number" id="total-refund" name="total_refund" readonly value="0.00">
          </div>

          <button type="submit" class="submit-btn">Confirm Return</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // Will be filled after sale lookup
  let saleItems = []; // [{stock_id, medicine, sale_unit_price, purchase_price, sold_qty}]
  let saleInfo  = null;

  // Build options from saleItems
  function buildMedicineOptions() {
    return saleItems.map(it =>
      `<option value="${it.medicine}"
               data-price="${it.sale_unit_price}"
               data-pp="${it.purchase_price}"
               data-max="${it.sold_qty}">
        ${it.medicine} (sold: ${it.sold_qty})
      </option>`
    ).join('');
  }

  function addReturnRow() {
    if (saleItems.length === 0) {
      alert('Load a valid Sale ID first to fetch items.');
      return;
    }

    const container = document.getElementById('return-items-container');
    const row = document.createElement('div');
    row.className = 'sreturn-grid-row';
    row.innerHTML = `
      <div class="sreturn-form-group">
        <label>Medicine</label>
        <select name="stock[]" required onchange="onMedicineChange(this)">
          <option value="" disabled selected>Select item</option>
          ${buildMedicineOptions()}
        </select>
      </div>

      <div class="sreturn-form-group">
        <label>Quantity</label>
        <input type="number" name="quantity[]" min="1" value="1" oninput="calculateRefund(this)">
      </div>

      <div class="sreturn-form-group">
        <label>Unit Price</label>
        <input type="number" name="unit_price[]" step="0.01" value="0.00" readonly>
      </div>

      <div class="sreturn-form-group">
        <label>Total</label>
        <input type="number" name="total[]" step="0.01" value="0.00" readonly>
      </div>

      <div class="sreturn-form-group">
        <label>&nbsp;</label>
        <button type="button" class="delete-btn" onclick="removeRow(this)">❌</button>
      </div>
    `;

    container.appendChild(row);
  }

  function onMedicineChange(selectEl) {
    const row = selectEl.closest('.sreturn-grid-row');
    const price = parseFloat(selectEl.options[selectEl.selectedIndex]?.dataset.price || 0);
    const max   = parseInt(selectEl.options[selectEl.selectedIndex]?.dataset.max || 0);
    const qtyEl = row.querySelector('[name="quantity[]"]');

    // Clamp quantity to max sold
    if (qtyEl.value === "" || parseInt(qtyEl.value) < 1) qtyEl.value = 1;
    if (max > 0) qtyEl.setAttribute('max', String(max));

    row.querySelector('[name="unit_price[]"]').value = price.toFixed(2);
    calculateRefund(qtyEl);
  }

  function calculateRefund(elem) {
    const row   = elem.closest('.sreturn-grid-row');
    const qtyEl = row.querySelector('[name="quantity[]"]');
    const sel   = row.querySelector('[name="stock_id[]"]');
    const price = parseFloat(sel.options[sel.selectedIndex]?.dataset.price || 0);
    const max   = parseInt(sel.options[sel.selectedIndex]?.dataset.max || 0);

    let qty = parseInt(qtyEl.value || "0");
    if (isNaN(qty) || qty < 1) qty = 1;
    if (max && qty > max) qty = max;
    qtyEl.value = qty;

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
    button.closest('.sreturn-grid-row').remove();
    updateRefundTotal();
  }

  async function fetchJSON(url) {
    const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
    return await r.json();
  }

  async function loadSale() {
    const saleId = document.getElementById('invoice_number').value.trim();
    const meta   = document.getElementById('sale_meta');

    if (!saleId) return;

    meta.textContent = 'Looking up sale...';
    meta.style.color = '#ccc';

    // 1) Lookup sale header
    let res = await fetchJSON(`?action=sale_lookup&sale_id=${encodeURIComponent(saleId)}`);
    if (!res.ok) {
      meta.textContent = res.error || 'Sale not found';
      meta.style.color = '#ff8080';
      saleItems = [];
      return;
    }
    saleInfo = res.sale;

    // Set/lock customer
const customerSelect = document.getElementById('customer_id');
customerSelect.value = String(saleInfo.customer_id);
customerSelect.setAttribute('disabled', 'disabled');

// Add hidden input so value is posted
let hidden = document.querySelector('input[name="customer_id"]');
if (!hidden) {
  hidden = document.createElement('input');
  hidden.type = 'hidden';
  hidden.name = 'customer_id';
  document.getElementById('salesReturnForm').appendChild(hidden);
}
hidden.value = saleInfo.customer_id;

    meta.textContent = `Customer: ${saleInfo.customer_name || 'N/A'} • Date: ${saleInfo.sale_date}`;
    meta.style.color = '#9ad29a';

    // 2) Load items from this sale
    res = await fetchJSON(`?action=sale_items&sale_id=${encodeURIComponent(saleId)}`);
    if (!res.ok) {
      meta.textContent = res.error || 'Could not load sale items';
      meta.style.color = '#ff8080';
      saleItems = [];
      return;
    }
    saleItems = res.items || [];

    // Reset current rows and totals
    document.getElementById('return-items-container').innerHTML = '';
    document.getElementById('total-refund').value = '0.00';
  }

  // Load sale on blur/enter of invoice field
  document.getElementById('invoice_number').addEventListener('change', loadSale);
  document.getElementById('invoice_number').addEventListener('blur', loadSale);
  document.getElementById('invoice_number').addEventListener('keyup', (e) => {
    if (e.key === 'Enter') loadSale();
  });

  // Optional: add one row only after sale items are loaded.
</script>

<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
