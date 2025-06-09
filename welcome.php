<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$dashboard = $_SESSION['role'] === 'admin' ? 'home.php' : 'HOMEEMP.php';
$firstName = htmlspecialchars($_SESSION['firstName']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="welcome.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="assets\logo.png" />
  <title>Asian College EIS</title>
</head>
<body>

<div class="container">
    <div class="logo-title-container">
        <div class="logo">
            <img src="assets/logo.png" alt="Asian College Logo" />
        </div>
        <div class="title">
            <h3>Asian College EIS</h3>
        </div>
    </div>

    <h1 class="welcome-text">Welcome, <?php echo $firstName; ?>!</h1>
    <p class="fade-in delayed-1">You have successfully logged in to the <strong>Employee Information System</strong>.</p>
    <a href="<?php echo $dashboard; ?>" class="btn fade-in delayed-2">Proceed</a>
</div>

</body>
</html>