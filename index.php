<?php
session_start();
$showLogin = false;
$selectedRole = '';

// Display error if login fails
if (isset($_SESSION['login_error'])) {
    $showLogin = true;
    if (isset($_SESSION['last_role'])) {
        $selectedRole = $_SESSION['last_role'];
    }
    unset($_SESSION['login_error']);
    unset($_SESSION['last_role']);
}

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php if (isset($_SESSION['reset_message'])): ?>
    <div class="reset-message"><?php echo $_SESSION['reset_message']; ?></div>
    <?php unset($_SESSION['reset_message']); ?>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Cache-Control" content="no-store" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Asian College EIS</title>
  <link rel="stylesheet" href="index.css" />
  <link rel="icon" href="assets/logo.png" />
</head>
<body>
  <div class="container">
    <header>
      <div class="logo">
        <img src="assets/logo.png" alt="Asian College Logo" />
      </div>
      <div class="title">
        <h1>Asian College EIS</h1>
      </div>
    </header>

    <section class="role-selection">
      <h2>Select Your Role</h2>
      <select id="role-select">
        <option value="" disabled selected>Select Role</option>
        <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="employee" <?= $selectedRole === 'employee' ? 'selected' : '' ?>>Employee</option>
      </select>
    </section>

    <section id="login-form" class="login <?= $showLogin ? '' : 'hidden' ?>">
      <?php if ($showLogin): ?>
        <div class="error-message">⚠️ Invalid email, ID, or role.</div>
      <?php endif; ?>

      <form action="login.php" method="post" autocomplete="off">
        <input type="hidden" name="role" id="role-input" value="<?= htmlspecialchars($selectedRole) ?>" />
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />

        <div class="login-title">
          <h2>Login</h2>
        </div>

        <div class="login-input">
          <label for="email">Email:</label>
          <input type="text" name="email" id="email" placeholder="Enter your email" required />
        </div>

        <div class="login-input">
          <label for="id">Password (or ID):</label>
          <input type="password" name="id" id="id" placeholder="Enter your ID or password" maxlength="50" required onpaste="return false;" />
          <label class="show-id-toggle">
            <input type="checkbox" id="show-id" /> Show Password/ID
          </label>
        </div>

        <div class="login-options">
          <a href="#" onclick="document.getElementById('forgotModal').style.display='block'">🔑 Forgot Password?</a>
        </div>

        <div class="login-button">
          <button type="submit" name="login">Login</button>
        </div>
      </form>
    </section>
  </div>

  <!-- Forgot Password Modal -->
  <div id="forgotModal" class="modal hidden">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('forgotModal').style.display='none'">&times;</span>
      <h3>Forgot Password</h3>
      <form action="forgot_password.php" method="post">
        <label for="resetEmail">Enter your registered email:</label>
        <input type="email" id="resetEmail" name="resetEmail" required />
        <button type="submit">Send Reset Link</button>
      </form>
    </div>
  </div>

  <script>
    const roleSelect = document.getElementById('role-select');
    const loginForm = document.getElementById('login-form');
    const roleInput = document.getElementById('role-input');
    const showIDCheckbox = document.getElementById('show-id');
    const idInput = document.getElementById('id');

    roleSelect.addEventListener('change', () => {
      const selectedRole = roleSelect.value;
      if (selectedRole === 'admin' || selectedRole === 'employee') {
        roleInput.value = selectedRole;
        loginForm.classList.remove('hidden');
      }
    });

    showIDCheckbox.addEventListener('change', () => {
      idInput.type = showIDCheckbox.checked ? 'text' : 'password';
    });

    window.addEventListener('DOMContentLoaded', () => {
      if (roleSelect.value) {
        loginForm.classList.remove('hidden');
      }
    });
  </script>

  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      padding-top: 60px;
      left: 0; top: 0;
      width: 100%; height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
      background-color: #fefefe;
      margin: auto;
      padding: 20px;
      border-radius: 8px;
      width: 80%;
      max-width: 400px;
      position: relative;
    }

    .modal-content h3 {
      margin-top: 0;
    }

    .modal-content .close {
      position: absolute;
      top: 10px; right: 20px;
      color: #aaa;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
    }

    .modal-content .close:hover {
      color: red;
    }

    .modal-content form input,
    .modal-content form button {
      width: 100%;
      padding: 8px;
      margin-top: 10px;
    }

    .login-options {
      text-align: right;
      margin-top: 5px;
    }

    .login-options a {
      font-size: 14px;
      color: #007bff;
      text-decoration: none;
    }

    .login-options a:hover {
      text-decoration: underline;
    }
  </style>
</body>
</html>
