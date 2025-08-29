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
// include "../includes/dbconnection.php";

$popup = false; 
if (isset($_POST['addsupplier'])) {
    $name  = $_POST['name'];
    $contact  = $_POST['contact'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $addr  = $_POST['address'];

    $customer = $conn->prepare("INSERT INTO supplier(name, contact_person, phone, email, address) VALUES(?, ?, ?, ?, ?)");
    $customer->bind_param("sssss", $name, $contact, $phone, $email, $addr);

    if ($customer->execute()) {
        $popup = true; 
    } else {
        echo "<div style='color:red;'>Error: " . $customer->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Supplier</title>
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
    <h2>Add Supplier</h2>
    <form id="addSupplierForm" method="POST">
      <div class="form-group">
        <label for="supplier-name">Supplier Name</label>
        <input type="text" id="supplier-name" name="name" required placeholder="Enter full name">
      </div>

      <div class="form-group">
        <label for="contact-person">Contact Person</label>
        <input type="text" id="contact-person" name="contact" required placeholder="Enter full name">
      </div>

      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required placeholder="Enter phone number">
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter email address">
      </div>

      <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address" rows="3" placeholder="Enter supplier address" required></textarea>
      </div>

      <button type="submit" class="submit-btn"  name="addsupplier">Add Supplier</button>
    </form>
  </div>

  <audio id="click">
  <source src="../images/success.mp3" type="audio/mpeg">
</audio>
<?php if (!empty($popup)) : ?>
<script>
    document.getElementById('click').play();
</script>
<?php endif; ?>

  <script>
<?php if ($popup): ?>
  window.onload = function() {
    Swal.fire({
      title: '🏆 Successful! 🏆',
      text: 'Your supplier has been saved successfully.',
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
        // 🎉 Start confetti when popup opens
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
      document.getElementById("addSupplierForm").reset();
    });
  };
<?php endif; ?>
</script>
</body>

</html>
<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
