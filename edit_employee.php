<?php
include 'db.php';

if (isset($_GET['id'])) {
  $employeeID = $_GET['id'];
  $query = "SELECT * FROM employeeuser WHERE employeeID = '$employeeID'";
  $result = mysqli_query($conn, $query);
  $row = mysqli_fetch_assoc($result);
}

if (isset($_POST['update'])) {
  $newEmployeeID = $_POST['employeeID'];
  $firstName = ucfirst($_POST['firstname']);
  $middleName = ucfirst($_POST['middlename']);
  $lastName = ucfirst($_POST['lastname']);
  $email = $_POST['email'];
  $position = ucfirst($_POST['position']);
  $sex = $_POST['sex'];

  $imagePath = $row['picture'];  
  if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) { 
    $imgName = basename($_FILES['picture']['name']);
    $tmpName = $_FILES['picture']['tmp_name'];
    $uploadDir = "uploads/";
    $imagePath = $uploadDir . time() . "_" . $imgName;

    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    move_uploaded_file($tmpName, $imagePath);
  }

  $updateQuery = "UPDATE employeeuser SET 
    employeeID = '$newEmployeeID',
    firstName = '$firstName', 
    middleName = '$middleName', 
    lastName = '$lastName', 
    email = '$email', 
    department = '$department', 
    sex = '$sex', 
    picture = '$imagePath' 
    WHERE employeeID = '$employeeID'";
    
  if (mysqli_query($conn, $updateQuery)) {
    header("Location: employee.php");
    exit();
  } else {
    echo "Error updating record: " . mysqli_error($conn);
  }
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="editADMIN.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets\logo.png">
  <title>Edit | Asian College EIS</title>
</head>

<body>
<nav class="top-nav">
    <h2><strong style="color: red;">Asian</strong> <strong style="color: blue;">College</strong> EIS Admin</h2>
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
  <section id="edit-admin">
    <h1>✏️ Edit Employee</h1>
    <form method="POST" enctype="multipart/form-data">
      
      
      <label for="firstname">First Name:</label>
      <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($row['firstName']) ?>" required>
      
      <label for="middlename">Middle Name:</label>
      <input type="text" id="middlename" name="middlename" value="<?= htmlspecialchars($row['middleName']) ?>" required>
      
      <label for="lastname">Last Name:</label>
      <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($row['lastName']) ?>" required>
      
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
      
      <label for="department">Department:</label>
        <select id="department" name="department" required>
          <option value="">-- Select Department --</option>
          <option value="DPD">DPD</option>
          <option value="CCSE">CCSE</option>
          <option value="CBAA">CBAA</option>
          <option value="CTHM">CTHM</option>
          <option value="SHS">SHS</option>
        </select>
     
      
      <button type="submit" name="update">Update Employee</button>
    </form>
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

  document.getElementById('profile').onchange = function (event) {
    const [file] = this.files;
    if (file) {
    const preview = document.getElementById('previewImg');
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
    }
  };
  </script>
</body>
</html>