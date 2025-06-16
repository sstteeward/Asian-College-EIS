<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$role = $_SESSION['role'];
$table = $role === 'employee' ? 'admin_' : 'employeeuser';

$query = "SELECT * FROM $table WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $middleName = trim($_POST['middleName']);
    $lastName = trim($_POST['lastName']);
    $department = trim($_POST['department']);
    $status = trim($_POST['status']);
    $contactNumber = trim($_POST['contactNumber']);
    $address = trim($_POST['address']);

    if (!empty($_FILES["picture"]["name"])) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["picture"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($fileType), $allowedTypes)) {
            move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFilePath);
        } else {
            $fileName = $user['picture']; 
        }
    } else {
        $fileName = $user['picture']; 
    }

    $updateQuery = "UPDATE $table SET firstName=?, middleName=?, lastName=?, department=?, status=?, contactNumber=?, address=?, picture=? WHERE email=?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssssss", $firstName, $middleName, $lastName, $department, $status, $contactNumber, $address, $fileName, $email);
    $stmt->execute();
    $stmt->close();

    header("Location: VIEWPROFEMP.php");
    exit();

    
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="editProfile.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets/LOGO for title.png">
  <title>Asian College EIS</title>
</head>
<body>
  <nav class="top-nav">
    <h2>Asian College EIS</h2>
    <img src="assets/logo2-removebg-preview.png" alt="Logo">
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets/menuIcon.png" alt="Menu Button" />
      <ul id="menuItems" class="menuItems">
        <li><a href="HOMEEMP.php" class="<?= $currentPage == 'HOMEEMP.php' ? 'active' : '' ?>">🏠 Home</a></li>
        <li><a href="NOTIFEMP.php" class="<?= $currentPage == 'NOTIFEMP.php' ? 'active' : '' ?>">🔔 Notifications</a></li>
        <li><a href="EMPLOYEEEMP.php" class="<?= $currentPage == 'EMPLOYEEEMP.php' ? 'active' : '' ?>">👨‍💼 Employee</a></li>
        <li><a href="VIEWPROFEMP.php" class="<?= $currentPage == 'VIEWPROFEMP.php' ? 'active' : '' ?>">👤 Profile</a></li>
      </ul>
    </div>
  </nav>

  <div class="profile-container">
    <h1>✏️ Edit Profile</h1>
    <form method="POST" enctype="multipart/form-data" class="profile-box">
      <div class="profile-picture">
        <img src="uploads/<?php echo htmlspecialchars($user['picture']); ?>" alt="Current Picture" style="width:120px;height:120px;border-radius:50%;">
        <input type="file" name="picture" accept="image/*">
      </div>

      <div class="profile-details">
        <label>First Name:</label>
        <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>

        <label>Middle Name:</label>
        <input type="text" name="middleName" value="<?php echo htmlspecialchars($user['middleName']); ?>">

        <label>Last Name:</label>
        <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>

        <label for="department">Department:</label>
        <select id="department" name="department" required>
          <option value="">-- Select Department --</option>
          <option value="DPD">DPD</option>
          <option value="CCSE">CCSE</option>
          <option value="CBAA">CBAA</option>
          <option value="CTHM">CTHM</option>
          <option value="SHS">SHS</option>
        </select>

        <label>Status:</label>
        <input type="text" name="status" value="<?php echo htmlspecialchars($user['status']); ?>">

        <label>Contact Number:</label>
        <input type="text" name="contactNumber" value="<?php echo htmlspecialchars($user['contactNumber']); ?>">

        <label>Address:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">

        <br><br>
        <input type="submit" value="💾 Save Changes" class="btn">
        <a href="profile.php" class="btn btn-logout">❌ Cancel</a>
      </div>
    </form>
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
</body>
</html>