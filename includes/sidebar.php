<body>
  <?php
          
          // if(isset($_POST['logout'])) {
          //   session_destroy();
          //   header('Location: ../pages/loginform.php');
          //   exit();
          // }
        ?>
  <?php include "dbconnection.php"; ?>
    <div class="container-scroller">
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <!-- <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
          <a class="sidebar-brand brand-logo" href="#"><img src="images/rainstar.png" alt="logo" /></a>
        </div> -->
        <div class="rainpoint">
          <a  href="pharmacist_dashboard.php"><img class="img-fluid" src="../images/rainstar.png" alt="logo" /></a>
        </div>
        <span class="cnam">RainStar</span>
        <ul class="nav">
          
          <li class="nav-item menu-items">
            <a class="nav-link" href="pharmacist_dashboard.php">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          <li class="nav-item menu-items" id="menuadd">
            <a class="nav-link" href="#">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Add</span>
            </a>
              <ul id="menuadd-sub">
                <li>
                  <a class="" href="registration_form.php">
                    <span class="">
                      <i class="mdi mdi-speedometer"></i>
                    </span>
                    <span class="sub-text">Add User</span>
                  </a>
              </li>

              <li>
                  <a class="" href="add_customer.php">
                    <span class="">
                      <i class="mdi mdi-speedometer"></i>
                    </span>
                    <span class="sub-text">Add Customer</span>
                  </a>
              </li>

              <li>
                  <a class="" href="add_supplier.php">
                    <span class="">
                      <i class="mdi mdi-speedometer"></i>
                    </span>
                    <span class="sub-text">Add Supplier</span>
                  </a>
              </li>
            </ul>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="sale_return.php">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Sale_Return</span>
            </a>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="purchase_return.php">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Purchase_Return</span>
            </a>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="stock.php">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Stock</span>
            </a>
          </li>

          <li class="nav-item menu-items" id="menuadd">
            <a class="nav-link" href="#">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Sale History</span>
            </a>
            <ul id="menuadd-sub">
                <li>
                  <a class="" href="allsales.php">
                    <span class="">
                      <i class="mdi mdi-speedometer"></i>
                    </span>
                    <span class="sub-text">All sales</span>
                  </a>
              </li>

              <li>
                  <a class="" href="sale_items.php">
                    <span class="">
                      <i class="mdi mdi-speedometer"></i>
                    </span>
                    <span class="sub-text">Sale items</span>
                  </a>
              </li>
            </ul>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="#">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Purchase History</span>
            </a>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="#">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Users</span>
            </a>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="#">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Regular Customers</span>
            </a>
          </li>

          
         
        </ul>
      </nav>