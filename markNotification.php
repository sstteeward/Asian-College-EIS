<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggleRead'])) {
    $employeeID = mysqli_real_escape_string($conn, $_POST['employeeID']);
    $role = $_POST['role'];

    if ($role === 'Admin') {
        $table = 'admin_';
    } elseif ($role === 'Employee') {
        $table = 'employeeuser';
    } else {
        header('Location: notifications.php');
        exit;
    }

    $sqlSelect = "SELECT readStatus FROM $table WHERE employeeID = '$employeeID' LIMIT 1";
    $result = mysqli_query($conn, $sqlSelect);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $currentStatus = (int)$row['readStatus'];
        $newStatus = $currentStatus === 1 ? 0 : 1;

        $sqlUpdate = "UPDATE $table SET readStatus = $newStatus WHERE employeeID = '$employeeID'";
        mysqli_query($conn, $sqlUpdate);
    }

    $filter = $_GET['filter'] ?? 'all';
    header("Location: notifications.php?filter=$filter");
    exit;
} else {
    header('Location: notifications.php');
    exit;
}