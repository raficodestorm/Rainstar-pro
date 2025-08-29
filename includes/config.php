<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pharmacist_id    = $_SESSION['id'] ?? null;
$pharmacist_name  = $_SESSION['username'] ?? null;
// you can also put common settings here
date_default_timezone_set("Asia/Dhaka");
?>