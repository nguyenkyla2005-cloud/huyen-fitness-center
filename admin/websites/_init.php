<?php
require_once __DIR__ . '/../qlkh/db.php'; // provides $pdo and $conn
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header("Location: ../../login-role/login/login.php");
    exit;
}

require_once __DIR__ . '/ensure_tables.php';
