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

function highlight($text, $search) {
    if (empty($search)) return htmlspecialchars($text);
    return preg_replace("/(" . preg_quote($search, '/') . ")/i", '<mark>$1</mark>', htmlspecialchars($text));
}

function displayUserRows($result, &$counter, $search, $role) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = urlencode($row['employeeID']);
        $isAdmin = $role === 'admin';

        echo "<tr>";
        echo "<td>{$counter}</td>";

        echo "<td>";
        if (!empty($row['picture']) && file_exists('uploads/' . $row['picture'])) {
            $pic = htmlspecialchars($row['picture']);
            echo "<img src='uploads/$pic' alt='Profile' style='width:50px; height:50px; border-radius:50%; object-fit:cover;'>";
        } else {
            echo generateLetterAvatar($row['firstName'][0]);
        }
        echo "</td>";

        echo "<td>" . htmlspecialchars($row['employeeID']) . "</td>";
        echo "<td>" . highlight($row['firstName'], $search) . "</td>";
        echo "<td>" . highlight($row['middleName'], $search) . "</td>";
        echo "<td>" . highlight($row['lastName'], $search) . "</td>";
        echo "<td>" . highlight($row['email'], $search) . "</td>";
        echo "<td>" . highlight($row['department'], $search) . "</td>";
        echo "<td>
                <a href='" . ($isAdmin ? "editADMIN.php" : "editADMIN.php") . "?id=$id'>
                  <button class='action-btn edit-btn'>Edit</button>
                </a>
                <a href='" . ($isAdmin ? "deleteADMIN.php" : "delete_employee.php") . "?id=$id' onclick='return confirm(\"Are you sure you want to delete this {$role}?\");'>
                  <button class='action-btn delete-btn'>Delete</button>
                </a>
              </td>";
        echo "</tr>";
        $counter++;
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$search = trim($_GET['search'] ?? '');
$safe_search = mysqli_real_escape_string($conn, $search);
$search_clause = "";

if (!empty($search)) {
    $like = "'%$safe_search%'";
    $search_clause = "WHERE 
        employeeID LIKE $like OR
        firstName LIKE $like OR
        middleName LIKE $like OR
        lastName LIKE $like OR
        email LIKE $like OR
        department LIKE $like";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="employee.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="assets/logo.png" />
  <title>Asian College EIS Admin</title>
  <style>
    .sticky-search {
      position: sticky;
      top: 70px;
      background: #fff;
      z-index: 100;
      padding: 20px 0;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

  <nav class="top-nav">
    <h2><strong style="color: red;">Asian</strong> <strong style="color: blue;">College</strong> EIS Admin</h2>
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

  <!-- 🔍 Sticky Search Bar -->
  <section class="sticky-search" style="text-align: center;">
    <form method="GET" action="">
      <input 
        type="text" 
        name="search" 
        placeholder="Search by name, ID, email, department..." 
        value="<?= htmlspecialchars($search) ?>" 
        style="padding: 10px; width: 250px; border-radius: 8px; border: 1px solid #ccc; font-size: 16px;" 
      />
      <button 
        type="submit" 
        style="padding: 10px 20px; background-color: #3498db; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;">
        🔍 Search
      </button>
    </form>
  </section>

  <section id="admin-list">
    <h1 style="text-align: center; margin-top: 20px;">
      <?= $search ? "Search Results for: <em>" . htmlspecialchars($search) . "</em>" : "All Users (Admins & Employees)" ?>
    </h1>

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
          <th>Department</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $counter = 1;
        $admin_result = mysqli_query($conn, "SELECT * FROM admin_ $search_clause");
        $employee_result = mysqli_query($conn, "SELECT * FROM employeeuser $search_clause");

        $admin_found = $admin_result && mysqli_num_rows($admin_result) > 0;
        $employee_found = $employee_result && mysqli_num_rows($employee_result) > 0;

        if ($admin_found) {
            echo "<tr><td colspan='9' style='background:#f0f0f0; text-align:center; font-weight:bold;'>Admins</td></tr>";
            displayUserRows($admin_result, $counter, $search, 'admin');
        }

        if ($employee_found) {
            echo "<tr><td colspan='9' style='background:#f0f0f0; text-align:center; font-weight:bold;'>Employees</td></tr>";
            displayUserRows($employee_result, $counter, $search, 'employee');
        }

        if (!$admin_found && !$employee_found) {
            echo "<tr><td colspan='9' style='text-align:center;'>No matching results found.</td></tr>";
        }

        mysqli_close($conn);
        ?>
      </tbody>
    </table>
  </section>
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

</body>
</html>
