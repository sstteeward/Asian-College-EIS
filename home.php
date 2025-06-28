<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Session timeout (30 mins)
$timeoutDuration = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeoutDuration) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

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


$completion = 0;
if (!empty($profilePic)) $completion += 25;
if (!empty($email)) $completion += 25;
if (!empty($lastLogin)) $completion += 25;
if (!empty($name)) $completion += 25;

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

$notifResult = mysqli_query($conn, "SELECT message, created_at FROM notifications ORDER BY created_at DESC LIMIT 3");

$currentPage = basename($_SERVER['PHP_SELF']);
$hour = date('H');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
$dayMessage = match (true) {
    $hour < 12 => "Hope you have a productive morning!",
    $hour < 18 => "Keep going, you're doing great this afternoon!",
    default => "Winding down? Here's your evening summary!"
};
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
      flex-wrap: wrap;
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
      flex-wrap: wrap;
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

    @media (max-width: 768px) {
      .stats {
        flex-direction: column;
      }

      .menuItems {
        flex-direction: column;
        gap: 0.5rem;
      }

      .chart-container {
        max-width: 100%;
      }
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
      <small>Last login: <?= $lastLogin ? date('m/d/Y h:i A', strtotime($lastLogin)) : 'First time login'; ?></small><br>
      <small><?= $dayMessage ?></small>
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
    <div class="card">
      <p><strong>Profile Completion</strong></p>
      <progress value="<?= $completion ?>" max="100" style="width: 100%;"></progress>
      <small><?= $completion ?>% complete</small>
    </div>
  </div>

  <div class="chart-container">
    <canvas id="userChart"></canvas>
  </div>

  <div class="quick-actions">
    <a href="addemployee.php">➕ Add New Employee</a>
    <a href="employee.php">👨‍💼 View Employees</a>
    <a href="notifications.php">🔔 View Notifications</a>
    <a href="logs.php">📜 View Logs</a>
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

  <div class="recent-logins">
    <h3>🔔 Recent Notifications</h3>
    <?php while($notif = mysqli_fetch_assoc($notifResult)): ?>
      <div class="recent-item">
        <p><?= htmlspecialchars($notif['message']) ?></p>
        <small><?= date('m/d/Y g:i A', strtotime($notif['created_at'])) ?></small>
      </div>
    <?php endwhile; ?>
  </div>
</div>

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
    <p>&copy; <?= date("Y") ?> <strong style="color: red;">Asian</strong> <strong style="color: blue;">College</strong>. All rights reserved.
      <a href="mailto:stewardhumiwat@gmail.com" style="font-weight: bold;">IT Department</a>
    </p>
    <div>
      <a href="profile.php">👤 Profile</a> |
      <a href="mailto:edfaburada.student@asiancollege.edu.ph">❓ Help</a> |
      <a href="mailto:jdacademia.student@asiancollege.edu.ph">📝 Feedback</a> |
      <a href="#" onclick="confirmLogout()">🚪 Logout</a>
    </div>
    <small style="display:block;margin-top:10px;">Build v1.0.0 | Updated: <?= date("F Y"); ?></small>
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
