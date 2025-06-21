<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Asia/Manila');
$email = $_SESSION['email'];

// Fetch admin info
$query = "SELECT firstName, picture, last_login FROM admin_ WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($firstName, $profilePic, $lastLogin);
$stmt->fetch();
$stmt->close();

$name = htmlspecialchars($firstName);
$displayPic = $profilePic && file_exists("uploads/" . $profilePic) ? "uploads/" . $profilePic : "avatar.php?name=" . urlencode($name);

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

$logins = mysqli_query($conn, "
    SELECT firstName, lastName, last_login 
    FROM admin_ 
    WHERE last_login IS NOT NULL 
    ORDER BY last_login DESC 
    LIMIT 5
");

$currentPage = basename($_SERVER['PHP_SELF']);
$hour = date('H');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="home.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="assets/logo.png" />
  <title>Asian College EIS</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
    }

    .top-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: white;
      padding: 1rem 2rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .menuItems {
      list-style: none;
      display: flex;
      gap: 1rem;
    }

    .menuItems a {
      text-decoration: none;
      color: #333;
      padding: 0.4rem 0.8rem;
      border-bottom: 2px solid transparent;
    }

    .menuItems a.active {
      border-bottom: 2px solid red;
      font-weight: bold;
    }

    .dashboard {
      padding: 2rem;
    }

    .welcome-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .profile-thumbnail {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #ccc;
    }

    .stats {
      display: flex;
      gap: 2rem;
      margin-top: 1.5rem;
      flex-wrap: wrap;
    }

    .card {
      background: #fff;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      flex: 1;
      min-width: 150px;
      text-align: center;
      transition: 0.2s ease-in-out;
    }

    .card:hover {
      transform: scale(1.03);
    }

    .quick-actions {
      margin-top: 2rem;
    }

    .quick-actions a {
      display: inline-block;
      background: #3498db;
      color: white;
      padding: 0.6rem 1rem;
      margin: 0.3rem;
      border-radius: 8px;
      text-decoration: none;
      transition: 0.3s ease;
    }

    .quick-actions a:hover {
      background: #2980b9;
    }

    .recent, .recent-logins {
      margin-top: 2.5rem;
    }

    .recent-item {
      background: #ffffff;
      border-left: 4px solid #3498db;
      padding: 0.7rem 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      transition: all 0.3s ease-in-out;
    }

    .recent-item:hover {
      background: #f1f1f1;
    }

    .chart-container {
      max-width: 300px;
      margin: 2rem auto;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .modal-box {
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      text-align: center;
      max-width: 400px;
      width: 90%;
      animation: fadeIn 0.3s ease-in-out;
    }

    .modal-buttons {
      margin-top: 1.5rem;
      display: flex;
      justify-content: center;
      gap: 1rem;
    }

    .btn-confirm, .btn-cancel {
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    .btn-confirm {
      background-color: red;
      color: #fff;
    }

    .btn-confirm:hover {
      background-color: darkred;
    }

    .btn-cancel {
      background-color: #bdc3c7;
      color: #333;
    }

    .btn-cancel:hover {
      background-color: #95a5a6;
    }

    @keyframes fadeIn {
      from { transform: scale(0.95); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    .footer {
      background: #f1f1f1;
      padding: 1rem;
      text-align: center;
      margin-top: 3rem;
    }

    .footer a {
      color: #007BFF;
      text-decoration: none;
    }

    .footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <nav class="top-nav">
    <h2><strong style="color: red;">Asian</strong> <strong style="color: blue;">College</strong> EIS Admin</h2>
    <ul class="menuItems">
      <li><a href="home.php" class="<?= $currentPage == 'home.php' ? 'active' : '' ?>">🏠 Home</a></li>
      <li><a href="notifications.php" class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">🔔 Notifications</a></li>
      <li><a href="employee.php" class="<?= $currentPage == 'employee.php' ? 'active' : '' ?>">👨‍💼 Employee</a></li>
      <li><a href="addemployee.php" class="<?= $currentPage == 'addemployee.php' ? 'active' : '' ?>">➕ Add New Employee</a></li>
      <li><a href="profile.php" class="<?= $currentPage == 'profile.php' ? 'active' : '' ?>">👤 Profile</a></li>
    </ul>
  </nav>

  <div class="dashboard">
    <div class="welcome-header">
      <img src="<?= $displayPic ?>" alt="Profile Picture" class="profile-thumbnail" />
      <div>
        <h1><?= $greeting ?>, <?= $name ?> 👋</h1>
        <small>Last login: <?= $lastLogin ? date('m/d/Y h:i A', strtotime($lastLogin)) : 'First time login'; ?></small>
      </div>
    </div>

    <div class="stats">
      <div class="card">
        <h2><?= $totalAdmins ?></h2>
        <p>Total Admins</p>
      </div>
      <div class="card">
        <h2><?= $totalEmployees ?></h2>
        <p>Total Employees</p>
      </div>
    </div>

    <div class="chart-container">
      <canvas id="userChart"></canvas>
    </div>

    <div class="quick-actions">
      <a href="addemployee.php">➕ Add New Employee</a>
      <a href="employee.php">👨‍💼 View Employees</a>
      <a href="notifications.php">🔔 View Notifications</a>
    </div>

    <div class="recent">
      <h3>🕒 Recent Activity</h3>
      <?php while($row = mysqli_fetch_assoc($recentResult)): ?>
        <div class="recent-item">
          <p><strong><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></strong> added as <?= $row['role'] ?></p>
          <small><?= date('m/d/Y g:i A', strtotime($row['registryDate'])) ?></small>
        </div>
      <?php endwhile; ?>
    </div>

    <div class="recent-logins">
      <h3>🧾 Recent Logins</h3>
      <?php while($log = mysqli_fetch_assoc($logins)): ?>
        <div class="recent-item">
          <p><strong><?= htmlspecialchars($log['firstName'] . ' ' . $log['lastName']) ?></strong></p>
          <small><?= date('m/d/Y g:i A', strtotime($log['last_login'])) ?></small>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Logout Modal -->
  <div id="logoutModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Confirm Logout</h3>
      <p>Are you sure you want to logout?</p>
      <div class="modal-buttons">
        <button onclick="proceedLogout()" class="btn-confirm">Yes, Logout</button>
        <button onclick="closeLogoutModal()" class="btn-cancel">Cancel</button>
      </div>
    </div>
  </div>

  <footer class="footer">
  <div class="footer-content">
    <div class="footer-section">
      <p>&copy; <?php echo date("Y"); ?> <strong style="color: red;">Asian</strong> <strong style="color: blue;">College</strong>. All rights reserved.</p>
       <a href="mailto:stewardhumiwat@gmail.com" style="font-weight: bold; color: #007BFF; text-decoration: none;">
        IT Department
      </a>
    </div>

    <div class="footer-section quick-links">
      <a href="profile.php">👤 Profile</a>
      <a href="mailto:edfaburada.student@asiancollege.edu.ph">❓ Help</a>
      <a href="mailto:jdacademia.student@asiancollege.edu.ph">📝 Feedback</a>
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

  <script>
    function confirmLogout() {
      document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
      document.getElementById('logoutModal').style.display = 'none';
    }

    function proceedLogout() {
      window.location.href = "logout.php";
    }

    const ctx = document.getElementById('userChart').getContext('2d');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Admins', 'Employees'],
        datasets: [{
          data: [<?= $totalAdmins ?>, <?= $totalEmployees ?>],
          backgroundColor: ['#3498db', '#2ecc71'],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  </script>
</body>
</html>
