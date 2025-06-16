<?php
session_start();
include 'db.php';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_role'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['reset_email'];
$role = $_SESSION['reset_role'];
$table = $role === 'admin' ? 'admin_' : 'employeeuser';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if (strlen($newPassword) < 6) {
        $error = "⚠️ Password must be at least 6 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "❌ Passwords do not match.";
    } else {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // ⚠️ WARNING: You're storing the password in the employeeID field — it's better to use a separate 'password' column.
        $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        // Clear reset session and notify user
        unset($_SESSION['reset_email'], $_SESSION['reset_role']);
        $_SESSION['reset_success'] = "✅ Password successfully reset. Please login.";
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="container">
        <h2>🔐 Reset Password</h2>
        <?php if (isset($error)) echo "<div class='error-message'>$error</div>"; ?>
        <form method="POST">
            <label>New Password:</label>
            <input type="password" name="newPassword" required minlength="6" />
            <label>Confirm Password:</label>
            <input type="password" name="confirmPassword" required minlength="6" />
            <button type="submit">Reset Password</button>
        </form>
        <a href="index.php">← Back to Login</a>
    </div>
</body>
</html>
