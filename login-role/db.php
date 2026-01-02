<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect("localhost", "root", "", "login_role");

if (!$conn) {
    die("Kết nối thất bại");
}
