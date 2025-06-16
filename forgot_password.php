<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['resetEmail']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_message'] = "❌ Invalid email format.";
        header("Location: index.php");
        exit();
    }

    $tables = ['admin_' => 'admin', 'employeeuser' => 'employee'];
    foreach ($tables as $table => $role) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_role'] = $role;
            header("Location: reset_password.php");
            exit();
        }
    }

    $_SESSION['reset_message'] = "⚠️ Email not found in our records.";
    header("Location: index.php");
    exit();
}
?>
