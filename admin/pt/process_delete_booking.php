<?php
require 'db.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo->prepare("DELETE FROM pt_bookings WHERE id=?")->execute([$id]);
    header("Location: pt.php?msg=deleted");
}
?>