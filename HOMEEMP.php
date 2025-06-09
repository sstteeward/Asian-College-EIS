<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../index.php");
    exit();
}

date_default_timezone_set('Asia/Manila');
$email = $_SESSION['email'];

$query = "SELECT firstName FROM employeeuser WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($firstName);
$name = $stmt->fetch() ? $firstName : '';
$stmt->close();

$totalAdmins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM admin_"))['total'];
$totalEmployees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM employeeuser"))['total'];

$recentQuery = "
    SELECT firstName, lastName, registryDate, 'Admin' AS role FROM admin_
    UNION
    SELECT firstName, lastName, registryDate, 'Employee' AS role FROM employeeuser
    ORDER BY registryDate DESC
    LIMIT 5
";
$recentResult = mysqli_query($conn, $recentQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="home.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets\logo.png">
  <title>Asian College EIS</title>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS Home</h2>
    <img src="assets/logo2-removebg-preview.png" alt="Logo">
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets\black_menuIcon.png" alt="Menu Button" />
      <ul id="menuItems" class="menuItems">
        <li><a href="HOMEEMP.php">🏠 Home</a></li>
        <li><a href="NOTIFEMP.php">🔔 Notifications</a></li>
        <li><a href="EMPLOYEEEMP.php">🧑‍💼 Employee</a></li>
        <li><a href="VIEWPROFEMP.php">👤 Profile</a></li>
      </ul>
    </div>
  </nav>

  <div class="dashboard">
    <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>

    <div class="stats">
      <div class="card">
        <h2><?php echo $totalAdmins; ?></h2>
        <p>Total Admins</p>
      </div>
      <div class="card">
        <h2><?php echo $totalEmployees; ?></h2>
        <p>Total Employees</p>
      </div>
    </div>

    <div class="quick-actions">
      <a href="employee.php">👨‍💼 View Employees</a>
      <a href="notifications.php">🔔 View Notifications</a>
    </div>

    <div class="recent">
      <h3>🕒 Recent Activity</h3>
      <?php while($row = mysqli_fetch_assoc($recentResult)): ?>
        <div class="recent-item">
          <p><strong><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></strong> added as <?php echo $row['role']; ?></p>
          <small><?php echo date('m/d/Y g:i A', strtotime($row['registryDate'])); ?></small>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <script>
    const menuBtn = document.getElementById('menuBtn');
    const menuItems = document.getElementById('menuItems');

    let menuOpen = false;

    menuBtn.addEventListener('click', () => {
      menuOpen = !menuOpen;
      if (menuOpen) {
        menuBtn.src = 'assets/black_closeIcon.png'; 
        menuItems.classList.add('menuOpen');
      } else {
        menuBtn.src = 'assets/black_menuIcon.png'; 
        menuItems.classList.remove('menuOpen');
      }
    });

    menuItems.addEventListener('click', () => {
      menuOpen = false;
      menuBtn.src = 'assets/black_menuIcon.png';
      menuItems.classList.remove('menuOpen');
    });

    function confirmLogout() {
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
      }
    }
  </script>

<footer class="footer">
  <div class="footer-content">
    <div class="footer-section">
      <p>&copy; <?php echo date("Y"); ?> <strong>Asian College</strong>. All rights reserved.</p>
    </div>

    <div class="footer-section quick-links">
      <a href="profile.php">👤 Profile</a>
      <a href="mailto:stewardhumiwat@gmail.com">❓ Help</a>
      <a href="#" onclick="confirmLogout()">🚪 Logout</a>
    </div>

    <div class="footer-section social-links">
      <a href="https://www.instagram.com/asiancollegedgte/" target="_blank" rel="noopener" aria-label="Instagram">
        <svg class="social-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#E4405F">
          <path d="M7.75 2A5.75 5.75 0 002 7.75v8.5A5.75 5.75 0 007.75 22h8.5A5.75 5.75 0 0022 16.25v-8.5A5.75 5.75 0 0016.25 2h-8.5zm0 1.5h8.5a4.25 4.25 0 014.25 4.25v8.5a4.25 4.25 0 01-4.25 4.25h-8.5a4.25 4.25 0 01-4.25-4.25v-8.5a4.25 4.25 0 014.25-4.25zm4.25 3.75a4.5 4.5 0 100 9 4.5 4.5 0 000-9zm0 1.5a3 3 0 110 6 3 3 0 010-6zm4.75-.375a1.125 1.125 0 11-2.25 0 1.125 1.125 0 012.25 0z"/>
        </svg>
      </a>
      <a href="https://www.facebook.com/AsianCollegeDumaguete" target="_blank" rel="noopener" aria-label="Facebook">
        <svg class="social-icon" xmlns="http://www.w3.org/2000/svg" fill="#1877F2" viewBox="0 0 24 24">
          <path d="M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.406.593 24 1.325 24h11.495v-9.294H9.691v-3.622h3.129V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.466.099 2.796.143v3.24l-1.918.001c-1.504 0-1.796.715-1.796 1.763v2.31h3.588l-.467 3.622h-3.121V24h6.116c.73 0 1.324-.593 1.324-1.324V1.325c0-.732-.593-1.325-1.324-1.325z"/>
        </svg>
      </a>
      <a href="https://asiancollege.edu.ph" target="_blank" aria-label="Website">
        <img src="assets/cropped-favicon-512-192x192.png" alt="Website">
      </a>
    </div>
  </div>
</footer>

</body>
</html>