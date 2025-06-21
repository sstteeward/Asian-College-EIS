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
    $department = trim($_POST['department'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $sex = trim($_POST['sex'] ?? '');
    $password = trim($_POST['password'] ?? ''); 

    if (
        empty($id) || empty($firstName) || empty($lastName) || empty($email) ||
        empty($department) || empty($role) || empty($sex) || empty($password) 
    ) {
        header("Location: addemployee.php?error=missing");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: addemployee.php?error=invalid_email");
        exit();
    }

    $firstName = ucwords(strtolower(preg_replace('/\s+/', ' ', $firstName)));
    $middleName = ucwords(strtolower(preg_replace('/\s+/', ' ', $middleName)));
    $lastName = ucwords(strtolower(preg_replace('/\s+/', ' ', $lastName)));

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $table = strtolower($role) === 'admin' ? 'admin_' : 'employeeuser';

    $checkSql = "SELECT 1 FROM $table WHERE employeeID = ? OR email = ? LIMIT 1";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ss", $id, $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        header("Location: addemployee.php?duplicate=1");
        exit();
    }
    $stmtCheck->close();

    $adminEmail = $_SESSION['email'];
    $stmtAdmin = $conn->prepare("SELECT employeeID FROM admin_ WHERE email = ? LIMIT 1");
    $stmtAdmin->bind_param("s", $adminEmail);
    $stmtAdmin->execute();
    $stmtAdmin->bind_result($addedBy);
    $stmtAdmin->fetch();
    $stmtAdmin->close();

    if (!$addedBy) {
        header("Location: addemployee.php?error=admin_not_found");
        exit();
    }

    $insertSql = "INSERT INTO $table 
        (employeeID, firstName, middleName, lastName, email, department, sex, password, registryDate, addedBy) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    $stmtInsert = $conn->prepare($insertSql);
    $stmtInsert->bind_param("ssssssssi", $id, $firstName, $middleName, $lastName, $email, $department, $sex, $hashedPassword, $addedBy);

    if ($stmtInsert->execute()) {
        header("Location: addemployee.php?success=1");
        exit();
    } else {
        header("Location: addemployee.php?error=insert_failed");
        exit();
    }
} else {
    header("Location: addemployee.php");
    exit();
}
