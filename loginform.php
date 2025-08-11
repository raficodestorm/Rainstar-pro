<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Form</title>
  <style>
    * {
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
  }

  body {
    background: linear-gradient(135deg, #0d1117, #1a1f2b);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }

  .login-box {
    background: #161b22;
    padding: 30px 25px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    width: 100%;
    max-width: 400px;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }

  .login-box h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #e6edf3;
    font-weight: 500;
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    color: #a1b0c4;
    font-size: 14px;
  }

  input, select {
    width: 100%;
    padding: 10px;
    background: #0d1117;
    border: 1px solid #30363d;
    border-radius: 6px;
    font-size: 14px;
    color: #e6edf3;
    transition: border-color 0.3s, box-shadow 0.3s;
  }

  input:focus, select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.4);
    outline: none;
  }

  .submit-btn {
    width: 100%;
    padding: 10px;
    background: #3b82f6;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: transform 0.2s, background 0.3s, box-shadow 0.3s;
  }

  .submit-btn:hover {
    transform: translateY(-1px);
    background: #60a5fa;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
  }

  .links {
    text-align: center;
    margin-top: 15px;
  }

  .links a {
    margin: 0 10px;
    text-decoration: none;
    color: #3b82f6;
    font-size: 14px;
    transition: color 0.2s;
  }

  .links a:hover {
    color: #60a5fa;
    text-decoration: underline;
  }
  </style>
</head>
<body>

  <div class="login-box">
    <h2>Login</h2>
    <form action="loginform.php" method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
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
          <option value="pharmacist">pharmacist</option>
        </select>
      </div>

      <button type="submit" class="submit-btn">Login</button>

      <div class="links">
        <a href="registrationf.php">Register</a>
        |
        <a href="#">Forgot Password?</a>
      </div>
    </form>
  </div>

</body>
</html>
<?php
session_start();

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rainstar_pharma";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT id, username, password, role_name FROM pharmacist WHERE username = ? AND role_name = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $stmt->store_result();

    // If user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $dbUsername, $dbPassword, $dbRole);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $dbPassword)) {
            // Store user info in session
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $dbUsername;
            $_SESSION['role'] = $dbRole;

            // Redirect based on role
            if ($dbRole == 'pharmacist') {
                header("Location: pharmacist_dashboard.php");
            } elseif ($dbRole == 'admin') {
                header("Location: pharmacist_dashboard.php");
            }
            exit;
        } else {
            echo "<p style='color:red;'>Invalid password!</p>";
        }
    } else {
        echo "<p style='color:red;'>No user found with that username and role!</p>";
    }

    $stmt->close();
}

$conn->close();
?>
