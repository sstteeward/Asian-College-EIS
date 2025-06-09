<?php
include 'db.php';

// Helper function to generate letter avatar with colored circle
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="employee.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="assets/LOGO for title.png" />
  <title>Asian College EIS Admin - Employee List</title>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS Admin Employee</h2>
    <img src="assets/logo2-removebg-preview.png" alt="Logo" />
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets/menuIcon.png" alt="Menu Button" />
      <ul id="menuItems" class="menuItems">
        <li><a href="HOMEEMP.php">🏠 Home</a></li>
        <li><a href="NOTIFEMP.php">🔔 Notifications</a></li>
        <li><a href="EMPLOYEEEMP.php">🧑‍💼 Employee</a></li>
        <li><a href="VIEWPROFEMP.php">👤 Profile</a></li>
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
        menuBtn.src = 'assets/closeIcon.png'; 
        menuItems.classList.add('menuOpen');
      } else {
        menuBtn.src = 'assets/menuIcon.png'; 
        menuItems.classList.remove('menuOpen');
      }
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