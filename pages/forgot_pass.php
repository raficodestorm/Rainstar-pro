<?php
include "../includes/dbconnection.php";

$message = "";

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    // Lookup email from username + role
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE username = ? AND role_name = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $email);
        $stmt->fetch();

        // Generate OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime('+10 minutes'));

        // Save OTP and expiry
        $update = $conn->prepare("UPDATE users SET reset_otp = ?, otp_expiry = ? WHERE id = ?");
        $update->bind_param("ssi", $otp, $expiry, $id);
        $update->execute();

        // Send OTP via email
        $subject = "Password Reset OTP";
        $body = "Hello $username,\n\nYour OTP for password reset is: $otp\nIt expires in 10 minutes.";
        $headers = "From: no-reply@yourdomain.com";
        mail($email, $subject, $body, $headers);

        // Redirect to reset password page
        header("Location: reset_pass.php?user=$username&role=$role");
        exit;
    } else {
        $message = "<p style='color:red;text-align:center;'>User not found!</p>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<style>
/* Use same styling as login form */
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
input, select, button {
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
<h2>Forgot Password</h2>
<?php echo $message; ?>
<form method="POST">
  <input type="text" name="username" placeholder="Enter Username" required>
  <select name="role" required>
    <option value="" disabled selected hidden>Select Role</option>
    <option value="admin">Admin</option>
    <option value="pharmacist">Pharmacist</option>
  </select>
  <button type="submit">Send OTP</button>
</form>
</div>
</body>
</html>
