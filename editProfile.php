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

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $middleName = trim($_POST['middleName']);
    $lastName = trim($_POST['lastName']);
    $department = trim($_POST['department']);
    $status = trim($_POST['status']);
    $contactNumber = trim($_POST['contactNumber']);
    $address = trim($_POST['address']);

    // Handle profile picture
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

    // Change password if fields are filled
    $newPassword = trim($_POST['newPassword'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    if (!empty($newPassword) || !empty($confirmPassword)) {
        if ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
        } elseif (strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE $table SET firstName=?, middleName=?, lastName=?, department=?, status=?, contactNumber=?, address=?, picture=?, password=? WHERE email=?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssssssssss", $firstName, $middleName, $lastName, $department, $status, $contactNumber, $address, $fileName, $hashedPassword, $email);
            $stmt->execute();
            $stmt->close();
            $success = "Profile and password updated successfully.";
        }
    } else {
        $updateQuery = "UPDATE $table SET firstName=?, middleName=?, lastName=?, department=?, status=?, contactNumber=?, address=?, picture=? WHERE email=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssssssss", $firstName, $middleName, $lastName, $department, $status, $contactNumber, $address, $fileName, $email);
        $stmt->execute();
        $stmt->close();
        $success = "Profile updated successfully.";
    }

    // Reload updated user info
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
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
  <title>Asian College EIS Admin</title>
</head>
<body>
<nav class="top-nav">
    <h2>Asian College EIS Admin</h2>
    <img src="assets/logo2-removebg-preview.png" alt="Logo">
    <div class="menu">
      <img id="menuBtn" class="menuBtn" src="assets/menuIcon.png" alt="Menu Button" />
      <ul id="menuItems" class="menuItems">
        <li><a href="home.php" class="<?= $currentPage === 'home.php' ? 'active' : '' ?>">🏠 Home</a></li>
        <li><a href="notifications.php" class="<?= $currentPage === 'notifications.php' ? 'active' : '' ?>">🔔 Notifications</a></li>
        <li><a href="employee.php" class="<?= $currentPage === 'employee.php' ? 'active' : '' ?>">👨‍💼 Employee</a></li>
        <li><a href="addemployee.php" class="<?= $currentPage === 'addemployee.php' ? 'active' : '' ?>">➕ Add New Employee</a></li>
        <li><a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">👤 Profile</a></li>
      </ul>
    </div>
</nav>

<div class="profile-container">
    <h1>✏️ Edit Profile</h1>

    <?php if ($error): ?>
        <p style="color: red; text-align: center;"><?php echo $error; ?></p>
    <?php elseif ($success): ?>
        <p style="color: green; text-align: center;"><?php echo $success; ?></p>
    <?php endif; ?>

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
          <?php
            $departments = ['DPD', 'CCSE', 'CBAA', 'CTHM', 'SHS'];
            foreach ($departments as $dep) {
                $selected = $user['department'] === $dep ? 'selected' : '';
                echo "<option value=\"$dep\" $selected>$dep</option>";
            }
          ?>
        </select>

        <label>Status:</label>
        <input type="text" name="status" value="<?php echo htmlspecialchars($user['status']); ?>">

        <label>Contact Number:</label>
        <input type="text" name="contactNumber" value="<?php echo htmlspecialchars($user['contactNumber']); ?>">

        <label>Address:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">

        <hr style="margin: 20px 0;">

        <h3>🔐 Change Password</h3>
        <label>New Password:</label>
        <input type="password" name="newPassword" placeholder="Enter new password">

        <label>Confirm Password:</label>
        <input type="password" name="confirmPassword" placeholder="Re-enter new password">

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
  menuBtn.src = menuOpen ? 'assets/closeIcon.png' : 'assets/menuIcon.png';
  menuItems.classList.toggle('menuOpen');
});

menuItems.addEventListener('click', () => {
  menuOpen = false;
  menuBtn.src = 'assets/menuIcon.png';
  menuItems.classList.remove('menuOpen');
});
</script>
</body>
</html>
