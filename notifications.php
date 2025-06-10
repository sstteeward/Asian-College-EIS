<?php 
session_start();
include 'db.php';

date_default_timezone_set('Asia/Manila');

$filter = $_GET['filter'] ?? 'all';
$whereClauses = [];

if ($filter === 'today') {
    $whereClauses[] = "DATE(registryDate) = CURDATE()";
} elseif ($filter === 'week') {
    $whereClauses[] = "YEARWEEK(registryDate, 1) = YEARWEEK(CURDATE(), 1)";
}

if ($filter === 'read') {
    $whereClauses[] = "readStatus = 1";
} elseif ($filter === 'unread') {
    $whereClauses[] = "readStatus = 0";
}

$whereClause = '';
if (!empty($whereClauses)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereClauses);
}

$adminQuery = "SELECT employeeID, firstName, lastName, registryDate, readStatus, 'Admin' AS role FROM admin_ $whereClause";
$employeeQuery = "SELECT employeeID, firstName, lastName, registryDate, readStatus, 'Employee' AS role FROM employeeuser $whereClause";

$sql = "$adminQuery UNION $employeeQuery ORDER BY registryDate DESC";
$result = mysqli_query($conn, $sql);

function activeClass($filterName, $currentFilter) {
    return $filterName === $currentFilter ? 'active' : '';
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="notifications.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets\logo.png">
  <title>Asian College EIS Admin</title>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS Admin</h2>
    <img src="assets/logo2-removebg-preview.png" alt="Logo">
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets/black_menuIcon.png" alt="Menu Button" />
      <ul id="menuItems" class="menuItems">
        <li><a href="home.php" class="<?= $currentPage == 'home.php' ? 'active' : '' ?>">🏠 Home</a></li>
        <li><a href="notifications.php" class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">🔔 Notifications</a></li>
        <li><a href="employee.php" class="<?= $currentPage == 'employee.php' ? 'active' : '' ?>">👨‍💼 Employee</a></li>
        <li><a href="addemployee.php" class="<?= $currentPage == 'addemployee.php' ? 'active' : '' ?>">➕ Add New Employee</a></li>
        <li><a href="profile.php" class="<?= $currentPage == 'profile.php' ? 'active' : '' ?>">👤 Profile</a></li>
      </ul>
    </div>
  </nav>

  <div class="notification-container">
    <h1>🔔 Notifications</h1>

    <div class="filter-options">
      <a class="<?php echo activeClass('all', $filter); ?>" href="notifications.php?filter=all">📅 All</a>
      <a class="<?php echo activeClass('today', $filter); ?>" href="notifications.php?filter=today">🟢 Today</a>
      <a class="<?php echo activeClass('week', $filter); ?>" href="notifications.php?filter=week">🔵 This Week</a>
      <a class="<?php echo activeClass('read', $filter); ?>" href="notifications.php?filter=read">✅ Read</a>
      <a class="<?php echo activeClass('unread', $filter); ?>" href="notifications.php?filter=unread">🔴 Unread</a>
    </div>

    <div class="notification-list">
      <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="notification-item <?php echo $row['readStatus'] ? 'read' : 'unread'; ?>">
          <p><strong>New <?php echo htmlspecialchars($row['role']); ?> Added</strong></p>
          <p><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?> has been added as <?php echo htmlspecialchars($row['role']); ?>.</p>
          <small><?php echo date('m/d/Y g:i A', strtotime($row['registryDate'])); ?></small>

          <form method="POST" action="markNotification.php?filter=<?php echo urlencode($filter); ?>" style="margin-top: 5px;">
            <input type="hidden" name="employeeID" value="<?php echo $row['employeeID']; ?>">
            <input type="hidden" name="role" value="<?php echo $row['role']; ?>">
            <button type="submit" name="toggleRead" class="read-toggle-btn">
              <?php echo $row['readStatus'] ? 'Mark as Unread' : 'Mark as Read'; ?>
            </button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

    <!-- Logout Confirmation Modal -->
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
      document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
      document.getElementById('logoutModal').style.display = 'none';
    }

    function proceedLogout() {
      window.location.href = "logout.php";
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