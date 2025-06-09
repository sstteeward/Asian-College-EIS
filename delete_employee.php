<?php
include 'db.php';

if (isset($_GET['id'])) {
    $employeeID = $_GET['id'];
    $deleteQuery = "DELETE FROM employeeuser WHERE employeeID = '$employeeID'";

    if (mysqli_query($conn, $deleteQuery)) {
        header("Location: employee.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>