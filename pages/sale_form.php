<?php
session_start();
include "../includes/dbconnection.php";

    
    // Fetch medicines
    $medicinesData = [];
    $medicines = $conn->query("SELECT id AS stock_id, medicine_name, sale_price, quantity FROM stock");
    while ($row = $medicines->fetch_assoc()) {
        $medicinesData[] = $row;
    }

    // Fetch customers
    $customers = $conn->query("SELECT id, name FROM customers");

    // Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $grand_total = floatval($_POST['grand_total']);
    $stock_ids = $_POST['stock_id'];
    $medicines = $_POST['medicine_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['sale_price'];

    // 1. Get pharmacist ID from session
    if (!isset($_SESSION['id'])) {
        die("Error: No pharmacist logged in.");
    }
    $pharmacist_id = $_SESSION['id'];

    // 2. Find customer ID from name
    $stmt = $conn->prepare("SELECT id FROM customers WHERE name = ?");
    $stmt->bind_param("s", $customer_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $customer_id = $row['id'];
    } else {
        // If customer not found, insert new one
        $stmt = $conn->prepare("INSERT INTO customers (name) VALUES (?)");
        $stmt->bind_param("s", $customer_name);
        if (!$stmt->execute()) {
            die("Error inserting new customer: " . $stmt->error);
        }
        $customer_id = $stmt->insert_id;
        }

    try {
        $conn->begin_transaction();

        // Insert into sales with customer_id & pharmacist_id
        $stmt = $conn->prepare("INSERT INTO sales (customer_id, pharmacist_id, total_amount) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed for sales: " . $conn->error);
        }
        $stmt->bind_param("iid", $customer_id, $pharmacist_id, $grand_total);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for sales: " . $stmt->error);
        }
        $sale_id = $stmt->insert_id;

        // Insert sale items + update stock
        for ($i = 0; $i < count($stock_ids); $i++) {
            $stk_id = $stock_ids[$i];
            $med_name = $medicines[$i];
            $qty = $quantities[$i];
            $unit_price = $prices[$i];

            // Check stock
            $stmt = $conn->prepare("SELECT quantity FROM stock WHERE id = ?");
            $stmt->bind_param("i", $stk_id);
            $stmt->execute();
            $stock = $stmt->get_result()->fetch_assoc();
            if (!$stock) {
                throw new Exception("Stock ID $stk_id not found");
            }
            if ($stock['quantity'] < $qty) {
                throw new Exception("Not enough stock for medicine ID $stk_id");
            }

            // Insert sale item
            $stmt = $conn->prepare("INSERT INTO sale_items (sale_id, stock_id, medicine, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisid", $sale_id, $stk_id, $med_name, $qty, $unit_price);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed for sale_items: " . $stmt->error);
            }

            // Update stock
            $stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param("ii", $qty, $stk_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed for stock UPDATE: " . $stmt->error);
            }
        }

        $conn->commit();
        header("Location: payment1.php?sale_id=" . $sale_id);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<div class="container-fluid page-body-wrapper">
  <?php include "../includes/navbar.php"; ?>

  <div class="main-panel">
    <div class="content-wrapper">

   <?php
   

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Form</title>
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
      /* padding: 40px; */
    }

    .form-container {
      background: #191d24;
      backdrop-filter: blur(12px);
      border-radius: 14px;
      padding: 30px 40px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
      max-width: 900px;
      margin: auto;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .sale-msg{
      padding: auto;
      margin: auto;
      margin-bottom: 8px
      z-index:9999;
      height: 40px;
      width: 300px;
      background-color:rgba(129, 243, 108, 0.24);
      color: lightgreen;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 6px 12px rgba(148, 190, 70, 0.77);
      font-weight: bold;
      opacity: 0;
      transform: translateY(-20px);
      animation: fadeInOut 3s ease-in-out forwards;
      transition: all 0.5s ease;
      }
      @keyframes fadeInOut {
        0% {
            opacity: 0;
            transform: translateY(-20px);
        }
        10% {
            opacity: 1;
            transform: translateY(0);
        }
        90% {
            opacity: 1;
            transform: translateY(0);
        }
        100% {
            opacity: 0;
            transform: translateY(-20px);
        }
      }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #ffffff;
      letter-spacing: 0.5px;
    }

    .sale-form-group {
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

    .sale-grid-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) 50px;
      gap: 20px;
      margin-bottom: 15px;
      align-items: end;
    }

    .add-medicine-btn {
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

    .add-medicine-btn:hover {
      background: linear-gradient(135deg, #2ecc71, #239b56);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(46, 204, 113, 0.4);
    }

    .submit-sale {
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

    .submit-sale:hover {
      background: linear-gradient(135deg, #339af0, #1864ab);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
    }

    .sale-section-title {
      margin: 20px 0 10px;
      font-size: 18px;
      color: #bbbbbb;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 5px;
    }

    .sale-delete-btn {
      padding: 8px 10px;
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .sale-delete-btn:hover {
      background: linear-gradient(135deg, #ff6b6b, #d63031);
      transform: scale(1.05);
    }

    /* Mobile: single column */
    @media (max-width: 600px) {
      .sale-grid-row {
        grid-template-columns: 1fr;
      }
    }
</style>
</head>
<body>

<div class="form-container">
<h2>Quick Sales Entry</h2>
<form id="salesForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    <div class="sale-form-group">
        <label for="customer_name">Customer Name</label>
        <input list="customerList" name="customer_name" id="customer_name" required placeholder="Type or select">
        <datalist id="customerList">
            <?php while($row = $customers->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['name']); ?>"></option>
            <?php endwhile; ?>
        </datalist>
    </div>

    <div class="sale-section-title">Medicines</div>
    <div id="medicine-container">   </div>
    <button type="button" class="add-medicine-btn">+ Add Medicine</button>

    <div class="sale-form-group" style="margin-top:20px;">
        <label for="grand-total">Grand Total</label>
        <input type="number" id="grand-total" name="grand_total" readonly value="0.00">
    </div>

    <button type="submit" class="submit-sale">Confirm Sale</button>
</form>
</div>

<datalist id="medicineList">
    <?php foreach($medicinesData as $row): ?>
        <option value="<?= htmlspecialchars($row['medicine_name']); ?>" data-id="<?= $row['stock_id']; ?>" data-qty="<?= $row['quantity']; ?>" data-price="<?= $row['sale_price']; ?>"></option>
    <?php endforeach; ?>
</datalist>

<script>
const medicines = <?php echo json_encode($medicinesData); ?>;

class SalesForm {
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
            if (!confirm('Confirm this sale?')) e.preventDefault();
        });
    }

    addRow() {
        const row = document.createElement('div');
        row.className = 'sale-grid-row';
        row.innerHTML = `
            <div class="sale-form-group">
                <label>Medicine</label>
                <input list="medicineList" name="medicine_name[]" required placeholder="Select medicine">
                <input type="hidden" name="stock_id[]">
            </div>
            <div class="sale-form-group">
                <label>Quantity</label>
                <input type="number" name="quantity[]" min="1" value="1">
            </div>
            <div class="sale-form-group">
                <label>Unit Price</label>
                <input type="number" name="sale_price[]" step="0.01" value="0.00" readonly>
            </div>
            <div class="sale-form-group">
                <label>Total</label>
                <input type="number" name="total[]" step="0.01" value="0.00" readonly>
            </div>
            <div class="sale-form-group">
                <label>&nbsp;</label>
                <button type="button" class="sale-delete-btn">❌</button>
            </div>
        `;

        const medicineInput = row.querySelector('[name="medicine_name[]"]');
        const qtyInput = row.querySelector('[name="quantity[]"]');
        const unitInput = row.querySelector('[name="sale_price[]"]');
        const totalInput = row.querySelector('[name="total[]"]');
        const stockInput = row.querySelector('[name="stock_id[]"]');

        const updateRow = () => {
            const medName = medicineInput.value.trim();
            const qty = parseFloat(qtyInput.value) || 0;
            const medicine = this.medicines.find(m => m.medicine_name.toLowerCase() === medName.toLowerCase());
            if (!medicine) {
                unitInput.value = "0.00";
                totalInput.value = "0.00";
                stockInput.value = "";
                this.updateTotal();
                return;
            }
            if (qty > medicine.quantity) qtyInput.value = medicine.quantity; // prevent excess
            unitInput.value = parseFloat(medicine.sale_price).toFixed(2);
            totalInput.value = (qtyInput.value * medicine.sale_price).toFixed(2);
            stockInput.value = medicine.stock_id;
            this.updateTotal();
        };

        medicineInput.addEventListener('change', updateRow);
        qtyInput.addEventListener('input', updateRow);
        row.querySelector('.sale-delete-btn').addEventListener('click', () => {
            row.remove();
            this.updateTotal();
        });

        this.container.appendChild(row);
    }

    updateTotal() {
        let total = 0;
        this.container.querySelectorAll('[name="total[]"]').forEach(inp => total += parseFloat(inp.value) || 0);
        this.grandTotal.value = total.toFixed(2);
    }
}

window.addEventListener('DOMContentLoaded', () => {
    new SalesForm('salesForm', 'medicine-container', 'grand-total', medicines);
});

setTimeout(() => {
        document.querySelectorAll('.sale-msg').forEach(msg => {
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-20px)';
            setTimeout(() => msg.remove(), 2000); 
        });
    }, 2000);
</script>
</body>
</html>

    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->