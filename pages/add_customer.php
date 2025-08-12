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
  <title>Add Regular Customer</title>
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
      max-width: 600px;
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
    <h2>Add Regular Customer</h2>
    <form id="addCustomerForm">
      <div class="form-group">
        <label for="customer-name">Customer Name</label>
        <input type="text" id="customer-name" name="name" required placeholder="Enter full name">
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
        <textarea id="address" name="address" rows="3" placeholder="Enter customer address" required></textarea>
      </div>

      <button type="submit" class="submit-btn">Add Customer</button>
    </form>
  </div>

  <script>
    document.getElementById("addCustomerForm").addEventListener("submit", function(event) {
      event.preventDefault(); // Stop actual submission
      alert("Customer added successfully!");
      this.reset();
    });
  </script>
</body>
</html><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Regular Customer</title>
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
      max-width: 600px;
      margin: auto;
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
    <h2>Add Regular Customer</h2>
    <form id="addCustomerForm">
      <div class="form-group">
        <label for="customer-name">Customer Name</label>
        <input type="text" id="customer-name" name="name" required placeholder="Enter full name">
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
        <textarea id="address" name="address" rows="3" placeholder="Enter customer address" required></textarea>
      </div>

      <button type="submit" class="submit-btn">Add Customer</button>
    </form>
  </div>

  <script>
    document.getElementById("addCustomerForm").addEventListener("submit", function(event) {
      event.preventDefault(); // Stop actual submission
      alert("Customer added successfully!");
      this.reset();
    });
  </script>
</body>
</html>
<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
