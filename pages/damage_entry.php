<?php
require_once "../includes/config.php";
require_once "../includes/dbconnection.php";

// ---------- AJAX: return purchase_price & quantity for a medicine ----------
if (isset($_GET['medicine'])) {
    $medicine = trim((string)$_GET['medicine']);

    $stmt = $conn->prepare(
        "SELECT id, quantity, purchase_price
         FROM stock
         WHERE medicine_name = ? AND pharmacist_id = ?
         ORDER BY expiry_date ASC
         LIMIT 1"
    );
    $stmt->bind_param("si", $medicine, $pharmacist_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stockData = $res ? $res->fetch_assoc() : null;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($stockData ?: (object)[]);
    exit; // stop further HTML output for AJAX
}

// ---------- POST: handle damage form submit ----------
$popup = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['damage'])) {
    $medicine = trim($_POST['damage_item'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($medicine === '' || $quantity <= 0) {
        $error = 'Please select a product and enter a valid quantity.';
    } else {
        // fetch authoritative stock row
        $stmt = $conn->prepare(
            "SELECT id, quantity, purchase_price
             FROM stock
             WHERE medicine_name = ? AND pharmacist_id = ?
             ORDER BY expiry_date ASC
             LIMIT 1"
        );
        $stmt->bind_param("si", $medicine, $pharmacist_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $stockData = $res ? $res->fetch_assoc() : null;

        if (!$stockData) {
            $error = 'Item not found in your stock.';
        } elseif ((int)$stockData['quantity'] < $quantity) {
            $error = 'Not enough stock available. In hand: ' . (int)$stockData['quantity'];
        } else {
            // transaction: insert damage record + decrement stock
            $conn->begin_transaction();
            try {
                $stock_id = (int)$stockData['id'];
                $unit_price = (float)$stockData['purchase_price'];

                // Insert into damage (ensure your damage table has these columns)
                $insert = $conn->prepare(
                    "INSERT INTO damage
                     (stock_id, medicine, quantity, unit_price, description, pharmacist_id)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $insert->bind_param("isidis", $stock_id, $medicine, $quantity, $unit_price, $description, $pharmacist_id);
                $insert->execute();

                $total = $quantity*$unit_price;
                $uprev = $conn->prepare("UPDATE revenue SET amount = amount - ? WHERE pharmacist_id = ? AND DATE(date) = CURDATE()");
                $uprev->bind_param("di", $total, $pharmacist_id);
                $uprev->execute();

                // Deduct from stock
                $update = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
                $update->bind_param("ii", $quantity, $stock_id);
                $update->execute();

                $conn->commit();
                $popup = true;
            } catch (Throwable $e) {
                $conn->rollback();
                $error = 'Failed to save damage: ' . $e->getMessage();
            }
        }
    }
}

// Now include layout/header (safe because AJAX branch already exited)
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">
      <!-- content area start -->

      <style>
        /* trimmed down styles from your original */
        body { font-family: 'Segoe UI',sans-serif; background: linear-gradient(135deg,#0f0f0f,#1a1a1a); color:#e0e0e0; }
        .form-container { background:#12151e; border-radius:14px; padding:30px 40px; max-width:700px; margin:20px auto; border:1px solid rgba(255,255,255,.05); box-shadow:0 8px 30px rgba(0,0,0,.6); }
        h2 { text-align:center; margin-bottom:20px; color:#fff; }
        .form-group { display:flex; flex-direction:column; margin-bottom:16px; }
        label { margin-bottom:8px; color:#bdbdbd; font-weight:600; }
        input { padding:10px 12px; border-radius:8px; border:1px solid #333; background:rgba(40,40,40,.9); color:#fff; }
        .submit-btn { background:linear-gradient(135deg,#4dabf7,#1c7ed6); color:#fff; padding:12px; border:none; border-radius:10px; font-weight:600; cursor:pointer; width:100%;}
        .error { background:#3a0b0b; color:#ffb4b4; padding:10px; border-radius:8px; margin-bottom:12px; border:1px solid #5c1f24; }
        .small { font-size:13px; color:#cfcfcf; }
      </style>

      <div class="form-container">
        <h2>Damage Entry</h2>

        <?php if ($error): ?>
          <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="damageForm" method="POST" novalidate>
          <div class="form-group">
            <label for="damage_item">Damage item</label>
            <input type="text" list="damage_list" id="damage_item" name="damage_item" placeholder="Enter product name" required>
            <datalist id="damage_list">
              <?php
              // Datalist: only this pharmacist's in-stock medicines (quantity > 0)
              $dl = $conn->prepare("SELECT DISTINCT medicine_name FROM stock WHERE pharmacist_id = ? AND quantity > 0 ORDER BY medicine_name ASC");
              $dl->bind_param("i", $pharmacist_id);
              $dl->execute();
              $rs = $dl->get_result();
              while ($r = $rs->fetch_assoc()) {
                  echo "<option value=\"" . htmlspecialchars($r['medicine_name']) . "\"></option>";
              }
              ?>
            </datalist>
            <div class="small">Select a name from the list (only your in-stock items shown).</div>
          </div>

          <div class="form-group">
            <label for="quantity">Quantity <span id="available_qty" class="small" style="margin-left:8px;color:#9ef01a;"></span></label>
            <input type="number" id="quantity" name="quantity" required placeholder="Enter quantity" min="1">
          </div>

          <div class="form-group">
            <label for="unit_price">Unit Price (auto)</label>
            <input type="text" id="unit_price" name="unit_price" readonly>
          </div>

          <div class="form-group">
            <label for="description">Description (optional)</label>
            <input type="text" id="description" name="description" placeholder="e.g., broken seal / expired">
          </div>

          <button type="submit" id="damage_btn" class="submit-btn" name="damage">Submit</button>
        </form>
      </div>

      <audio id="click"><source src="../images/success.mp3" type="audio/mpeg"></audio>

      <script>
        const damageInput = document.getElementById('damage_item');
        const unitPriceInput = document.getElementById('unit_price');
        const qtyInput = document.getElementById('quantity');
        const availSpan = document.getElementById('available_qty');

        // Use 'input' so datalist selection triggers immediately
        damageInput.addEventListener('input', () => {
          const medicine = damageInput.value.trim();
          unitPriceInput.value = '';
          availSpan.textContent = '';
          qtyInput.removeAttribute('max');

          if (!medicine) return;

          fetch('?medicine=' + encodeURIComponent(medicine))
            .then(r => r.json())
            .then(data => {
              if (!data || Object.keys(data).length === 0) {
                unitPriceInput.value = '';
                availSpan.textContent = 'Not found';
                return;
              }
              unitPriceInput.value = (data.purchase_price !== undefined && data.purchase_price !== null) ? parseFloat(data.purchase_price).toFixed(2) : '';
              if (data.quantity !== undefined && data.quantity !== null) {
                qtyInput.setAttribute('max', data.quantity);
                availSpan.textContent = 'In hand: ' + data.quantity;
              } else {
                availSpan.textContent = '';
              }
            })
            .catch(err => {
              console.error(err);
              unitPriceInput.value = '';
              availSpan.textContent = 'Error';
            });
        });

        // prevent entering more than max
        qtyInput.addEventListener('input', () => {
          const max = parseInt(qtyInput.getAttribute('max') || '0', 10);
          if (max > 0 && qtyInput.value !== '' && parseInt(qtyInput.value, 10) > max) {
            qtyInput.value = max;
          }
        });
      </script>

      <script>
<?php if ($popup): ?>
  window.onload = function() {
    Swal.fire({
      title: '🏆 Successful!🏆',
      text: 'Damage record saved and stock updated.',
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
      document.getElementById("damage_btn").reset();
    });
  };
<?php endif; ?>
</script>
<?php if (!empty($popup)) : ?>
<script>
    document.getElementById('click').play();
</script>
<?php endif; ?>

      <!-- content area end -->
    </div> <!-- content-wrapper -->
    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
