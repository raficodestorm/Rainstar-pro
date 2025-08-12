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
  <title>registraition Form</title>
  <style>
   body {
  background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
  /* display: flex;
  justify-content: center;
  align-items: center; */
  /* min-height: 100vh; */
  font-family: 'Segoe UI', sans-serif;
  color: #e0e0e0;
}

.reg-box {
  background: #191d24;
  backdrop-filter: blur(12px);
  padding: 40px 30px;
  border-radius: 14px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
  max-width: 1000px;
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.reg-box h2 {
  text-align: center;
  margin-bottom: 30px;
  font-size: 1.8rem;
  color: #ffff;
  font-weight: bold;
  letter-spacing: 0.5px;
}

form {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  font-weight: 600;
  margin-bottom: 6px;
  color: #bdbdbd;
  font-size: 0.95rem;
}

input, select {
  padding: 12px;
  border: 1px solid #333;
  border-radius: 8px;
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

.submit-reg {
  grid-column: span 2;
  padding: 14px;
  background: linear-gradient(135deg, #4dabf7, #1c7ed6);
  border: none;
  color: white;
  font-size: 16px;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-reg:hover {
  background: linear-gradient(135deg, #339af0, #1864ab);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.links {
  grid-column: span 2;
  text-align: center;
  margin-top: 15px;
}

.links a {
  margin: 0 10px;
  text-decoration: none;
  color: #4dabf7;
  font-size: 14px;
  transition: color 0.3s ease;
}

.links a:hover {
  text-decoration: underline;
  color: #82cfff;
}

/* Mobile: single column */
@media (max-width: 600px) {
  form {
    grid-template-columns: 1fr;
  }

  .submit-reg,
  .links {
    grid-column: span 1;
  }
}



  </style>
</head>
<body>

  <div class="reg-box">
  <h2>User Registration</h2>
    <form action="#" method="POST" enctype="multipart/form-data">
    
      <div class="form-group">
        <label for="fullname">Fullname</label>
        <input type="text" id="fullname" name="fullname" required>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="username">Phone</label>
        <input type="number" id="phone" name="phone" required>
      </div>

      <div class="form-group">
        <label for="username">Branch</label>
        <input type="text" id="branch" name="branch" required>
      </div>

      

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="form-group">
        <label for="role">Select Role</label>
        <select name="role" id="role" required>
          <option value="" disabled selected hidden>Select role</option>
          <option value="admin">Admin</option>
          <option value="pharmacist">Pharmacist</option>
        </select>
      </div>

      <div class="form-group">
        <label for="image">Profile Image</label>
        <input type="file" id="image" name="image" accept="image/*" required>
    </div>

      <button type="submit" class="submit-reg">Submit</button>

      <div class="links">
        <a href="#">Login</a>
      </div>
    </form>
  </div>

</body>
</html>

<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rainstar_pharma";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form values
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $branch = trim($_POST['branch']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Handle image upload
    // Handle image upload
$imageName = $_FILES['image']['name'];
$imageTmp = $_FILES['image']['tmp_name'];
$imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

// Allowed file types
$allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

// Rename file to avoid overwriting
$newImageName = uniqid("IMG_", true) . '.' . $imageExt;
$imageFolder = "uploads/" . $newImageName;

if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
}

if (in_array($imageExt, $allowedExt) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
    if (move_uploaded_file($imageTmp, $imageFolder)) {
        // Insert with the new image name
        $stmt = $conn->prepare("INSERT INTO pharmacist (fullname, username, email, phone, branch, password, role_name, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $fullname, $username, $email, $phone, $branch, $password, $role, $newImageName);


        if ($stmt->execute()) {
            echo "<p style='color:green;'>Registration successful!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red;'>Image upload failed!</p>";
    }
} else {
    echo "<p style='color:red;'>Invalid file type or file too large (Max: 2MB)</p>";
}

}

$conn->close();
?>


<!-- contant area end----------------------------------------------------------------------------->
    </div> <!-- content-wrapper ends -->

    <?php include "../includes/footer.php"; ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
