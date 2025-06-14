<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $id = trim($_POST['id'] ?? '');
    $firstName = trim($_POST['firstName'] ?? '');
    $middleName = trim($_POST['middleName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $sex = trim($_POST['sex'] ?? '');

    if (empty($id) || empty($firstName) || empty($lastName) || empty($email) || empty($position) || empty($role) || empty($sex)) {
        alertAndRedirect('Please fill all required fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        alertAndRedirect('Invalid email address.');
    }

    // Format names: capitalize and remove extra spaces
    $firstName = ucwords(strtolower(preg_replace('/\s+/', ' ', $firstName)));
    $middleName = ucwords(strtolower(preg_replace('/\s+/', ' ', $middleName)));
    $lastName = ucwords(strtolower(preg_replace('/\s+/', ' ', $lastName)));

    $table = strtolower($role) === 'admin' ? 'admin_' : 'employeeuser';

    // Check for existing ID or Email
    $checkSql = "SELECT 1 FROM $table WHERE employeeID = ? OR email = ? LIMIT 1";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ss", $id, $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        alertAndRedirect("Employee ID or Email already exists in $role table.");
    }
    $stmtCheck->close();

    // Get admin who added the record
    $adminEmail = $_SESSION['email'];
    $stmtAdmin = $conn->prepare("SELECT employeeID FROM admin_ WHERE email = ? LIMIT 1");
    $stmtAdmin->bind_param("s", $adminEmail);
    $stmtAdmin->execute();
    $stmtAdmin->bind_result($addedBy);
    $stmtAdmin->fetch();
    $stmtAdmin->close();

    if (!$addedBy) {
        alertAndRedirect("Could not determine admin who is adding this user.");
    }

    // Insert new record
    $insertSql = "INSERT INTO $table (employeeID, firstName, middleName, lastName, email, position, sex, registryDate, addedBy) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmtInsert = $conn->prepare($insertSql);
    $stmtInsert->bind_param("sssssssi", $id, $firstName, $middleName, $lastName, $email, $position, $sex, $addedBy);

    if ($stmtInsert->execute()) {
        header("Location: addemployee.php?success=1");
        exit(); // ✅ Ensure proper redirect
    } else {
        alertAndRedirect("Error adding new $role: " . $stmtInsert->error);
    }
} else {
    header("Location: addemployee.php");
    exit();
}

function alertAndRedirect($message) {
    $msg = htmlspecialchars($message, ENT_QUOTES);
    echo "<script>alert('$msg'); window.history.back();</script>";
    exit();
}
