<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$query = "SELECT email, login_time FROM login_logs ORDER BY login_time DESC LIMIT 100";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="home.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="assets/logo.png" />
  <title>Asian College EIS </title>
</head>
<body>
  <div class="dashboard">
    <h1>📜 Login Logs</h1>

    <table>
      <thead>
        <tr>
          <th>Email</th>
          <th>Login Time</th>
        </tr>
      </thead>
      <tbody>
        <?php while($log = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($log['email']) ?></td>
            <td><?= date('m/d/Y h:i A', strtotime($log['login_time'])) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
