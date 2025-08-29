<?php
require_once "../includes/config.php"; 
require_once "../includes/dbconnection.php"; 

$message = "";
$username = isset($_GET['user']) ? $_GET['user'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);
    $new_password = $_POST['new_password'];

    // Verify OTP
    $stmt = $conn->prepare("SELECT id, otp_expiry FROM users WHERE username = ? AND role_name = ? AND reset_otp = ?");
    $stmt->bind_param("sss", $username, $role, $otp);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $otp_expiry);
        $stmt->fetch();

        if (strtotime($otp_expiry) >= time()) {
            // OTP valid, update password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, reset_otp = NULL, otp_expiry = NULL WHERE id = ?");
            $update->bind_param("si", $hashed, $id);
            $update->execute();

            $message = "<p style='color:green;text-align:center;'>Password updated successfully! <a href='loginform.php'>Login now</a></p>";
        } else {
            $message = "<p style='color:red;text-align:center;'>OTP expired!</p>";
        }
    } else {
        $message = "<p style='color:red;text-align:center;'>Invalid OTP!</p>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<style>
body {
  background: linear-gradient(135deg, #0d1117, #1a1f2b);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  font-family: 'Segoe UI', sans-serif;
}
.login-box {
  background: #161b22;
  padding: 30px;
  border-radius: 12px;
  width: 100%;
  max-width: 450px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}
.login-box h2 {
  text-align: center;
  color: #e6edf3;
  margin-bottom: 20px;
}
input, button {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border-radius: 6px;
  border: 1px solid #30363d;
  background: #0d1117;
  color: #e6edf3;
  font-size: 14px;
}
button {
  background: #3b82f6;
  border: none;
  cursor: pointer;
  transition: 0.2s;
}
button:hover {
  background: #60a5fa;
}
</style>
</head>
<body>

<div class="login-box">
<h2>Reset Password</h2>
<?php echo $message; ?>
<form method="POST">
  <input type="text" name="otp" placeholder="Enter OTP" required>
  <input type="password" name="new_password" placeholder="New Password" required>
  <button type="submit">Update Password</button>
</form>
</div>
</body>
</html>
