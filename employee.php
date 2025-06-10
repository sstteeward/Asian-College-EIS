<?php
include 'db.php';

function generateLetterAvatar($letter) {
    $colors = ['#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#e67e22', '#e74c3c'];
    $color = $colors[ord(strtoupper($letter)) % count($colors)];

    return "<div style='
        background-color: $color;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 24px;
        user-select: none;
    '>" . strtoupper($letter) . "</div>";
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="employee.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="assets\logo.png" />
  <title>Asian College EIS Admin</title>

  <style>
    .menuItems a.active {
      color: #E53E3E; 
      border-bottom: 3px solid #E53E3E;
      transition: all 0.3s ease;
    }
   
  </style>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS Admin Employee</h2>
    <img src="assets/logo2-removebg-preview.png" alt="Logo" />
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

  <div class="main-content">
    <!-- Admin List -->
    <section id="admin-list">
      <h1 style="text-align: center;">Admin List</h1>
      <table class="employee-table" cellspacing="0" cellpadding="5" border="1" style="width:100%; border-collapse: collapse;">
        <thead>
          <tr>
            <th>Number</th>
            <th>Picture</th>
            <th>Employee ID</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Position</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql_admins = "SELECT * FROM admin_";
          $result_admins = mysqli_query($conn, $sql_admins);
          if ($result_admins && mysqli_num_rows($result_admins) > 0) {
              $counter = 1;
              while ($row = mysqli_fetch_assoc($result_admins)) {
                  echo "<tr>";
                  echo "<td>" . $counter++ . "</td>";

                  // Show image or letter avatar for Admin
                  echo "<td>";
                  if (!empty($row['picture']) && file_exists('uploads/' . $row['picture'])) {
                      echo "<img src='uploads/" . htmlspecialchars($row['picture']) . "' alt='Profile' style='width:50px; height:50px; border-radius:50%; object-fit:cover;'>";
                  } else {
                      echo generateLetterAvatar($row['firstName'][0]);
                  }
                  echo "</td>";

                  echo "<td>" . htmlspecialchars($row['employeeID']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['middleName']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                  echo "<td>
                          <a href='editADMIN.php?id=" . urlencode($row['employeeID']) . "'>
                            <button class='action-btn edit-btn'>Edit</button>
                          </a>
                          <a href='deleteADMIN.php?id=" . urlencode($row['employeeID']) . "' onclick='return confirm(\"Are you sure you want to delete this Admin?\");'>
                            <button class='action-btn delete-btn'>Delete</button>
                          </a>
                        </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='9' style='text-align:center;'>No Admins found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>

    <!-- Employee List -->
    <section id="employee-list" style="margin-top: 60px;">
      <h1 style="text-align: center;">Employee List</h1>
      <table class="employee-table" cellspacing="0" cellpadding="5" border="1" style="width:100%; border-collapse: collapse;">
        <thead>
          <tr>
            <th>Number</th>
            <th>Picture</th>
            <th>Employee ID</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Position</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql_employees = "SELECT * FROM employeeuser";
          $result_employees = mysqli_query($conn, $sql_employees);
          if ($result_employees && mysqli_num_rows($result_employees) > 0) {
              $counter = 1;
              while ($row = mysqli_fetch_assoc($result_employees)) {
                  echo "<tr>";
                  echo "<td>" . $counter++ . "</td>";

                  // Show image or letter avatar for Employee
                  echo "<td>";
                  if (!empty($row['picture']) && file_exists('uploads/' . $row['picture'])) {
                      echo "<img src='uploads/" . htmlspecialchars($row['picture']) . "' alt='Profile' style='width:50px; height:50px; border-radius:50%; object-fit:cover;'>";
                  } else {
                      echo generateLetterAvatar($row['firstName'][0]);
                  }
                  echo "</td>";

                  echo "<td>" . htmlspecialchars($row['employeeID']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['middleName']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                  echo "<td>
                          <a href='edit_employee.php?id=" . urlencode($row['employeeID']) . "'>
                            <button class='action-btn edit-btn'>Edit</button>
                          </a>
                          <a href='delete_employee.php?id=" . urlencode($row['employeeID']) . "' onclick='return confirm(\"Are you sure you want to delete this Employee?\");'>
                            <button class='action-btn delete-btn'>Delete</button>
                          </a>
                        </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='9' style='text-align:center;'>No Employees found.</td></tr>";
          }
          mysqli_close($conn);
          ?>
        </tbody>
      </table>
    </section>
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
          <svg class="social-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#1877F2">
            <path d="M22 12a10 10 0 10-11.5 9.9v-7h-2v-3h2v-2c0-2 1-3 3-3h2v3h-2c-.5 0-1 .5-1 1v1h3l-.5 3h-2.5v7A10 10 0 0022 12z"/>
          </svg>
        </a>
        <a href="https://twitter.com/asiancollegedgte" target="_blank" rel="noopener" aria-label="Twitter">
          <svg class="social-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#1DA1F2">
            <path d="M22.46 6c-.77.35-1.6.59-2.46.69a4.32 4.32 0 001.88-2.38 8.59 8.59 0 01-2.73 1.04 4.3 4.3 0 00-7.33 3.92 12.2 12.2 0 01-8.85-4.5 4.3 4.3 0 001.33 5.74 4.25 4.25 0 01-1.95-.54v.05a4.3 4.3 0 003.45 4.22 4.3 4.3 0 01-1.94.07 4.3 4.3 0 004.01 3 8.6 8.6 0 01-5.32 1.83A8.76 8.76 0 012 19.54 12.14 12.14 0 008.29 21c7.55 0 11.68-6.26 11.68-11.69 0-.18-.01-.35-.02-.53A8.35 8.35 0 0022.46 6z"/>
          </svg>
        </a>
      </div>
    </div>
  </footer>
</body>
</html>
