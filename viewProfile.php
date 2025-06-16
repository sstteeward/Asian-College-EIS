<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$role = $_SESSION['role'];

$allowed_roles = ['admin', 'employee'];
if (!in_array($role, $allowed_roles)) {
    header("Location: logout.php");
    exit();
}

$table = $role === 'admin' ? 'admin_' : 'employeeuser';

$query = "SELECT * FROM $table WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$fields = ['firstName', 'middleName', 'lastName', 'email', 'employeeID', 'role', 'department',  'status', 'contactNumber', 'address', 'picture'];
$completed = 1;

foreach ($fields as $field) {
    $user[$field] = isset($user[$field]) ? trim($user[$field]) : '';

    if (in_array($field, ['firstName', 'middleName', 'lastName', 'address'])) {
        $user[$field] = ucwords(strtolower($user[$field]));
    }

    if (!empty($user[$field])) {
        $completed++;
    }
}

$completion = round(($completed / count($fields)) * 100);

$filename = !empty($user['picture']) ? $user['picture'] : 'default.png';
$profilePic = 'uploads/' . basename($filename);

$fullName = trim($user['firstName'] . ' ' . ($user['middleName'] ? $user['middleName'] . ' ' : '') . $user['lastName']);

// Get current page for active menu highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="profile.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="assets/logo.png" />
  <title>Asian College EIS Admin</title>
  <style>
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
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      max-width: 400px;
      width: 90%;
      animation: fadeIn 0.3s ease-in-out;
    }

    .modal-buttons {
      margin-top: 1.5rem;
      display: flex;
      justify-content: space-around;
      gap: 1rem;
    }

    .btn-confirm {
      background-color: red;
      color: #fff;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn-confirm:hover {
      background-color: darkred;
    }

    .btn-cancel {
      background-color: #bdc3c7;
      color: #333;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn-cancel:hover {
      background-color: #95a5a6;
    }

    @keyframes fadeIn {
      from { transform: scale(0.9); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    .menuItems li a.active {
      color: #e74c3c;
      font-weight: 700;
      border-bottom: 2px solid #e74c3c;
      transition: all 0.3s ease;
    }

    .menuItems li a:hover:not(.active) {
      color: #c0392b;
    }
  </style>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS</h2>>
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets/black_menuIcon.png" alt="Menu Button" role="button" aria-label="Toggle navigation menu" />
      <ul id="menuItems" class="menuItems">
        <li><a href="HOMEEMP.php" class="<?= $currentPage == 'HOMEEMP.php' ? 'active' : '' ?>">🏠 Home</a></li>
        <li><a href="NOTIFEMP.php" class="<?= $currentPage == 'NOTIFEMP.php' ? 'active' : '' ?>">🔔 Notifications</a></li>
        <li><a href="EMPLOYEEEMP.php" class="<?= $currentPage == 'EMPLOYEEEMP.php' ? 'active' : '' ?>">👨‍💼 Employee</a></li>
        <li><a href="VIEWPROFEMP.php" class="<?= $currentPage == 'VIEWPROFEMP.php' ? 'active' : '' ?>">👤 Profile</a></li>
      </ul>
    </div>
  </nav>

  <h1 style="margin-top: 6rem; text-align: center;">My Profile</h1>

  <div class="profile-container" style="margin: 2rem auto; max-width: 800px; padding: 20px; border-radius: 10px; background-color: #f9f9f9; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <div class="profile-box">
        <div class="profile-picture">
          <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" />
        </div>
        <div class="profile-details">
          <p><strong>Full Name:</strong> <?php echo htmlspecialchars($fullName); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
          <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($user['employeeID']); ?></p>
          <p><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($role)); ?></p>
          <p><strong>Department:</strong> <?php echo htmlspecialchars($user['department']); ?></p>
          <p><strong>Status:</strong> <?php echo htmlspecialchars($user['status']); ?></p>
          <p><strong>Date Joined:</strong> <?php echo htmlspecialchars(date("F d, Y", strtotime($user['registryDate']))); ?></p>
          <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($user['contactNumber']); ?></p>
          <p><strong>Address:</strong> <?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : 'N/A'; ?></p>
        </div>
      </div>

      <div class="profile-actions">
        <a href="EDITPROFEMP.php" class="btn">✏️ Edit Profile</a> 
        <a href="#" onclick="confirmLogout()" class="btn btn-logout">🚪 Logout</a>
      </div>

      <div class="profile-meter">
        <p>Profile Completion: <?php echo $completion; ?>%</p>
        <div class="meter">
          <div class="meter-fill" style="width: <?php echo $completion; ?>%;"></div>
        </div>
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
      menuBtn.src = menuOpen ? 'assets/black_closeIcon.png' : 'assets/black_menuIcon.png';
      menuItems.classList.toggle('menuOpen', menuOpen);
    });

    menuItems.addEventListener('click', () => {
      menuOpen = false;
      menuBtn.src = 'assets/black_menuIcon.png';
      menuItems.classList.remove('menuOpen');
    });

    window.addEventListener('DOMContentLoaded', () => {
      const meterFill = document.querySelector('.meter-fill');
      const width = meterFill.style.width;
      meterFill.style.width = '0%';
      setTimeout(() => {
        meterFill.style.width = width;
      }, 50);
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
        <a href="https://www.instagram.com/asiancollegedgte/" target="_blank" rel="noopener">
          <svg class="social-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#E4405F">
            <path d="M7.75 2A5.75 5.75 0 002 7.75v8.5A5.75 5.75 0 007.75 22h8.5A5.75 5.75 0 0022 16.25v-8.5A5.75 5.75 0 0016.25 2h-8.5zm0 1.5h8.5a4.25 4.25 0 014.25 4.25v8.5a4.25 4.25 0 01-4.25 4.25h-8.5a4.25 4.25 0 01-4.25-4.25v-8.5a4.25 4.25 0 014.25-4.25zm4.25 3.75a4.5 4.5 0 100 9 4.5 4.5 0 000-9zm0 1.5a3 3 0 110 6 3 3 0 010-6zm4.75-.375a1.125 1.125 0 11-2.25 0 1.125 1.125 0 012.25 0z"/>
          </svg>
        </a>
        <a href="https://www.facebook.com/AsianCollegeDumaguete" target="_blank" rel="noopener">
          <svg class="social-icon" xmlns="http://www.w3.org/2000/svg" fill="#1877F2" viewBox="0 0 24 24">
            <path d="M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.407.593 24 1.325 24h11.495v-9.294H9.691v-3.622h3.129V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.466.099 2.797.143v3.243l-1.918.001c-1.504 0-1.796.715-1.796 1.763v2.311h3.59l-.467 3.622h-3.123V24h6.116C23.407 24 24 23.407 24 22.676V1.325C24 .593 23.407 0 22.675 0z"/>
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
