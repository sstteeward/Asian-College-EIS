<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="style.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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
      justify-content: center;
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

    button[type="submit"] {
      background-color: red;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 15px;
      display: inline-block;
    }

    button[type="submit"]:hover {
      background-color: #E53E3E;
    }
  </style>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS Admin</h2>
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets/black_menuIcon.png" alt="Menu Button" />
      <ul id="menuItems" class="menuItems">
        <li><a href="home.php" class="<?= $currentPage === 'home.php' ? 'active' : '' ?>">🏠 Home</a></li>
        <li><a href="notifications.php" class="<?= $currentPage === 'notifications.php' ? 'active' : '' ?>">🔔 Notifications</a></li>
        <li><a href="employee.php" class="<?= $currentPage === 'employee.php' ? 'active' : '' ?>">👨‍💼 Employee</a></li>
        <li><a href="addemployee.php" class="<?= $currentPage === 'addemployee.php' ? 'active' : '' ?>">➕ Add New Employee</a></li>
        <li><a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">👤 Profile</a></li>
      </ul>
    </div>
  </nav>

  <div class="main-content">
    <section id="add-employee">
      <h1>➕ Add New Employee/Admin</h1>
      <form action="insert.php" method="post">
        <label for="id">Employee ID:</label>
        <input type="text" id="id" name="id" required />

        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" required />

        <label for="middleName">Middle Name:</label>
        <input type="text" id="middleName" name="middleName" />

        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" required />

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required />

        <label for="department">Department:</label>
        <select id="department" name="department" required>
          <option value="">-- Select Department --</option>
          <option value="TVET">TVET</option>
          <option value="CCSE">CCSE</option>
          <option value="CBAA">CBAA</option>
          <option value="CTHM">CTHM</option>
          <option value="SHS">SHS</option>
        </select>

        <label for="role">Role:</label>
        <select id="role" name="role" required>
          <option value="">-- Select Role --</option>
          <option value="Employee">Employee</option>
          <option value="Admin">Admin</option>
        </select>

        <label>Sex:</label>
        <div class="radio-group">
          <label class="radio-option">
            <input type="radio" id="male" name="sex" value="male" required />
            <span class="radio-custom"></span>
            <span class="radio-text">Male</span>
          </label>

          <label class="radio-option">
            <input type="radio" id="female" name="sex" value="female" required />
            <span class="radio-custom"></span>
            <span class="radio-text">Female</span>
          </label>
        </div>

        <button type="submit" name="submit">Add</button>
      </form>
    </section>
  </div>

  <!-- ✅ Success Modal -->
  <div id="addSuccessModal" class="modal-overlay">
    <div class="modal-box">
      <h3>🎉 Employee Added</h3>
      <p>The new employee has been successfully added.</p>
      <div class="modal-buttons">
        <button onclick="closeAddSuccessModal()" class="btn-confirm">OK</button>
      </div>
    </div>
  </div>

  <!-- ✅ Duplicate Error Modal -->
  <div id="duplicateErrorModal" class="modal-overlay">
    <div class="modal-box">
      <h3>⚠️ Duplicate Entry</h3>
      <p>Employee ID or Email already exists.</p>
      <div class="modal-buttons">
        <button onclick="closeDuplicateModal()" class="btn-confirm">OK</button>
      </div>
    </div>
  </div>

  <!-- ❌ Logout Modal -->
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
      menuItems.classList.toggle('menuOpen');
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

    // ✅ Show Modals Based on URL Parameters
    window.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('success') === '1') {
        document.getElementById('addSuccessModal').style.display = 'flex';
      } else if (urlParams.get('duplicate') === '1') {
        document.getElementById('duplicateErrorModal').style.display = 'flex';
      }

      const newUrl = window.location.origin + window.location.pathname;
      window.history.replaceState({}, document.title, newUrl);
    });

    function closeAddSuccessModal() {
      document.getElementById('addSuccessModal').style.display = 'none';
    }

    function closeDuplicateModal() {
      document.getElementById('duplicateErrorModal').style.display = 'none';
    }
  </script>

  <footer class="footer">
    <div class="footer-content">
      <div class="footer-section">
        <p>&copy; <?= date("Y") ?> <strong>Asian College</strong>. All rights reserved.</p>
      </div>
      <div class="footer-section quick-links">
        <a href="profile.php">👤 Profile</a>
        <a href="mailto:stewardhumiwat@gmail.com">❓ Help</a>
        <a href="#" onclick="confirmLogout()">🚪 Logout</a>
      </div>
      <div class="footer-section social-links">
        <a href="https://www.instagram.com/asiancollegedgte/" target="_blank" aria-label="Instagram">
          <svg class="social-icon" fill="#E4405F" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M7.75 2A5.75 5.75 0 002 7.75v8.5A5.75 5.75 0 007.75 22h8.5A5.75 5.75 0 0022 16.25v-8.5A5.75 5.75 0 0016.25 2h-8.5zm0 1.5h8.5a4.25 4.25 0 014.25 4.25v8.5a4.25 4.25 0 01-4.25 4.25h-8.5a4.25 4.25 0 01-4.25-4.25v-8.5a4.25 4.25 0 014.25-4.25zm4.25 3.75a4.5 4.5 0 100 9 4.5 4.5 0 000-9zm0 1.5a3 3 0 110 6 3 3 0 010-6zm4.75-.375a1.125 1.125 0 11-2.25 0 1.125 1.125 0 012.25 0z"/>
          </svg>
        </a>
        <a href="https://www.facebook.com/AsianCollegeDumaguete" target="_blank" aria-label="Facebook">
          <svg class="social-icon" fill="#1877F2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.406.593 24 1.325 24h11.495v-9.294H9.691v-3.622h3.129V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.466.099 2.796.143v3.24l-1.918.001c-1.504 0-1.796.715-1.796 1.763v2.31h3.588l-.467 3.622h-3.121V24h6.116c.73 0 1.324-.593 1.324-1.324V1.325c0-.732-.593-1.325-1.324-1.325z"/>
          </svg>
        </a>
        <a href="https://asiancollege.edu.ph" target="_blank" aria-label="Website">
          <img src="assets/cropped-favicon-512-192x192.png" alt="Website" />
        </a>
      </div>
    </div>
  </footer>
</body>
</html>
