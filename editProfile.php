<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$role = $_SESSION['role'];
$table = $role === 'admin' ? 'admin_' : 'employeeuser';

$query = "SELECT * FROM $table WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$passwordMessage = "";
$showPasswordForm = false;

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_changes'])) {
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

    header("Location: profile.php");
    exit();
}

// Change password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $showPasswordForm = true;

    $checkQuery = "SELECT password FROM $table WHERE email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($oldPassword, $row['password'])) {
        $passwordMessage = "<p class='error-msg'>❌ Incorrect old password.</p>";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordMessage = "<p class='error-msg'>❌ New passwords do not match.</p>";
    } elseif (strlen($newPassword) < 6) {
        $passwordMessage = "<p class='error-msg'>❌ Password must be at least 6 characters.</p>";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePass = "UPDATE $table SET password=? WHERE email=?";
        $stmt = $conn->prepare($updatePass);
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();
        $stmt->close();
        $passwordMessage = "<p class='success-msg'>✅ Password successfully changed!</p>";
        $showPasswordForm = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="EDITPROFEMP.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets/LOGO for title.png">
  <title>Asian College EIS</title>
  <style>
    .error-msg {
      color: red;
      font-weight: bold;
      margin: 10px 0;
    }
    .success-msg {
      color: green;
      font-weight: bold;
      margin: 10px 0;
    }
    #passwordForm {
      background: #f5f5f5;
      border: 1px solid #ccc;
      padding: 15px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
<nav class="top-nav">
  <h2>Asian College EIS Admin</h2>
  <img src="assets/logo2-removebg-preview.png" alt="Logo">
  <div class="menu">
    <img id="menuBtn" class="menuBtn" src="assets/menuIcon.png" alt="Menu Button" />
    <ul id="menuItems" class="menuItems">
      <li><a href="home.php" class="<?= $currentPage == 'home.php' ? 'active' : '' ?>">🏠 Home</a></li>
        <li><a href="notifications.php" class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">🔔 Notifications</a></li>
        <li><a href="employee.php" class="<?= $currentPage == 'employee.php' ? 'active' : '' ?>">👨‍💼 Employee</a></li>
        <li><a href="addemployee.php" class="<?= $currentPage == 'addemployee.php' ? 'active' : '' ?>">➕ Add New Employee</a></li>
        <li><a href="profile.php" class="<?= $currentPage == 'profile.php' ? 'active' : '' ?>">👤 Profile</a></li>
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

      <label>Department:</label>
      <select id="department" name="department" required>
        <option value="">-- Select Department --</option>
        <option value="DPD" <?= $user['department'] == 'DPD' ? 'selected' : '' ?>>DPD</option>
        <option value="CCSE" <?= $user['department'] == 'CCSE' ? 'selected' : '' ?>>CCSE</option>
        <option value="CBAA" <?= $user['department'] == 'CBAA' ? 'selected' : '' ?>>CBAA</option>
        <option value="CTHM" <?= $user['department'] == 'CTHM' ? 'selected' : '' ?>>CTHM</option>
        <option value="SHS" <?= $user['department'] == 'SHS' ? 'selected' : '' ?>>SHS</option>
      </select>

      <label>Status:</label>
      <input type="text" name="status" value="<?php echo htmlspecialchars($user['status']); ?>">

      <label>Contact Number:</label>
      <input type="text" name="contactNumber" value="<?php echo htmlspecialchars($user['contactNumber']); ?>">

      <label>Address:</label>
      <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">

      <br><br>
      <input type="submit" name="save_changes" value="💾 Save Changes" class="btn">
      <a href="profile.php" class="btn btn-logout">❌ Cancel</a>

      <br><br>
      <button type="button" onclick="togglePasswordForm()" class="btn" style="background-color:#ffc107;">🛠️ Change Password</button>

      <div id="passwordForm" style="display: <?= $showPasswordForm ? 'block' : 'none' ?>; margin-top:20px;">
        <h3>🔑 Change Password</h3>
        <?php echo $passwordMessage; ?>

        <label>Old Password:</label>
        <input type="password" name="oldPassword" required>

        <label>New Password:</label>
        <input type="password" name="newPassword" required>

        <label>Confirm New Password:</label>
        <input type="password" name="confirmPassword" required>

        <input type="submit" name="change_password" value="🔒 Change Password" class="btn">
      </div>
    </div>
  </form>
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

function togglePasswordForm() {
  const form = document.getElementById('passwordForm');
  form.style.display = form.style.display === "none" ? "block" : "none";
}
</script>
</body>
</html>