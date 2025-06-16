<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['resetEmail']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_message'] = "❌ Invalid email format.";
        header("Location: index.php");
        exit();
    }

    // Tables to check and corresponding roles
    $tables = ['admin_' => 'admin', 'employeeuser' => 'employee'];

    foreach ($tables as $table => $role) {
        $stmt = $conn->prepare("SELECT email FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email found
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_role'] = $role;
            header("Location: reset_password.php");
            exit();
        }
    }

    // Email not found in either table
    $_SESSION['reset_message'] = "⚠️ Email not found in our records.";
    header("Location: index.php");
    exit();
}
?>
