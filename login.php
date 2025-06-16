<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $inputID = trim($_POST['id']);
    $role = trim($_POST['role']);

    $_SESSION['last_role'] = $role;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'employee'])) {
        $_SESSION['login_error'] = true;
        header("Location: index.php");
        exit();
    }

    $table = $role === 'admin' ? 'admin_' : 'employeeuser';

    $stmt = $conn->prepare("SELECT firstName, employeeID FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($firstName, $hashedID);
        $stmt->fetch();

        if (password_verify($inputID, $hashedID)) {
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['firstName'] = $firstName;

            header("Location: welcome.php"); // Or employee dashboard
            exit();
        }
    }

    $_SESSION['login_error'] = true;
    header("Location: index.php");
    exit();
}
?>
