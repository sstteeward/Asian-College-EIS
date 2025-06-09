<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="style.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="assets\logo.png" />
  <title>Asian College EIS Admin</title>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS Admin Add Employee</h2>
    <img src="assets/logo2-removebg-preview.png" alt="Logo" />
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets/menuIcon.png" alt="Menu Button" />
      <ul id="menuItems" class="menuItems">
        <li><a href="home.php">🏠 Home</a></li>
        <li><a href="notifications.php">🔔 Notifications</a></li>
        <li><a href="employee.php">👨‍💼 Employee</a></li>
        <li><a href="addemployee.php">➕ Add New Employee</a></li>
        <li><a href="profile.php">👤 Profile</a></li>
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

        <label for="position">Position:</label>
        <input type="text" id="position" name="position" required />

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

  <script>
    const menuBtn = document.getElementById('menuBtn');
    const menuItems = document.getElementById('menuItems');
    let menuOpen = false;

    menuBtn.addEventListener('click', () => {
      menuOpen = !menuOpen;
      menuBtn.src = menuOpen ? 'assets/closeIcon.png' : 'assets/menuIcon.png';
      menuItems.classList.toggle('menuOpen');
    });

    menuItems.addEventListener('click', () => {
      menuOpen = false;
      menuBtn.src = 'assets/menuIcon.png';
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