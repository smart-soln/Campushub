<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "campus_hub";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Fetch all admin usernames
$adminUsers = [];
$result = $conn->query("SELECT username FROM admin");
while ($row = $result->fetch_assoc()) {
    $adminUsers[] = $row['username'];
}

$addAdminMsg = $changePassMsg = "";
$addAdminMsgType = $changePassMsgType = "";

// Add New Admin
if (isset($_POST['add_admin'])) {
    $new_username = $conn->real_escape_string($_POST['new_username']);
    $new_password = $conn->real_escape_string($_POST['new_password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $addAdminMsg = "Passwords do not match!";
        $addAdminMsgType = "error";
    } else {
        $check = $conn->query("SELECT * FROM admin WHERE username = '$new_username'");
        if ($check->num_rows > 0) {
            $addAdminMsg = "Username already exists!";
            $addAdminMsgType = "error";
        } else {
            // Store password in plain text (not recommended for production)
            $conn->query("INSERT INTO admin (username, password) VALUES ('$new_username', '$new_password')");
            $addAdminMsg = "New admin added successfully!";
            $addAdminMsgType = "success";
            // Refresh admin list
            $adminUsers = [];
            $result = $conn->query("SELECT username FROM admin");
            while ($row = $result->fetch_assoc()) {
                $adminUsers[] = $row['username'];
            }
        }
    }
}

// Change Password
if (isset($_POST['change_password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $old_password = $conn->real_escape_string($_POST['old_password']);
    $new_password = $conn->real_escape_string($_POST['new_password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $changePassMsg = "New passwords do not match!";
        $changePassMsgType = "error";
    } else {
        $check = $conn->query("SELECT * FROM admin WHERE username = '$username'");
        if ($check->num_rows === 1) {
            $row = $check->fetch_assoc();
            // Compare plain text passwords (not recommended for production)
            if ($old_password === $row['password']) {
                // Store new password in plain text (not recommended for production)
                $conn->query("UPDATE admin SET password = '$new_password' WHERE username = '$username'");
                $changePassMsg = "Password updated successfully!";
                $changePassMsgType = "success";
            } else {
                $changePassMsg = "Old password is incorrect!";
                $changePassMsgType = "error";
            }
        } else {
            $changePassMsg = "Username not found!";
            $changePassMsgType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Admin Settings | CampusHub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --accent: #f72585;
      --success: #4cc9f0;
      --error: #f44336;
      --warning: #ff9800;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --card-bg: rgba(255, 255, 255, 0.95);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      color: var(--dark);
      min-height: 100vh;
      line-height: 1.5;
    }
    
    header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 1rem;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    
    header h1 {
      font-size: clamp(1.2rem, 4vw, 1.5rem);
      font-weight: 600;
      padding: 0 2.5rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .back-btn {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: white;
      background: rgba(255, 255, 255, 0.2);
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }
    
    .back-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }
    
    .container {
      padding: 1rem;
      max-width: 800px;
      margin: 0 auto;
    }
    
    .card {
      background: var(--card-bg);
      border-radius: 10px;
      padding: 1.25rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 1.5rem;
      border: 1px solid rgba(0, 0, 0, 0.05);
      position: relative;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--primary);
    }
    
    .btn-group {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 1.25rem;
      flex-wrap: wrap;
    }
    
    .btn-toggle {
      padding: 0.75rem 1rem;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.2s ease;
      cursor: pointer;
      border: none;
      background: var(--primary);
      color: white;
      display: flex;
      align-items: center;
      gap: 6px;
      flex: 1 1 45%;
      min-width: 120px;
      justify-content: center;
    }
    
    .btn-toggle:hover {
      background: var(--secondary);
    }
    
    .btn-toggle i {
      font-size: 0.9rem;
    }
    
    .form-section {
      display: none;
      animation: fadeIn 0.3s ease;
    }
    
    .form-section.active {
      display: block;
    }
    
    .form-group {
      margin-bottom: 1.25rem;
    }
    
    label {
      display: block;
      margin-bottom: 0.4rem;
      font-weight: 500;
      color: var(--dark);
      font-size: 0.95rem;
    }
    
    /* Enhanced Select Dropdown */
    .select-wrapper {
      position: relative;
      width: 100%;
    }
    
    .select-wrapper::after {
      content: '\f078';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      pointer-events: none;
      color: var(--gray);
      font-size: 0.8rem;
    }
    
    select {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 6px;
      font-family: inherit;
      font-size: 0.95rem;
      transition: all 0.2s ease;
      background: white;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      cursor: pointer;
    }
    
    input {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 6px;
      font-family: inherit;
      font-size: 0.95rem;
      transition: all 0.2s ease;
      background: white;
    }
    
    input:focus, select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
    
    /* Password Strength Meter */
    .password-strength {
      margin-top: 0.4rem;
      height: 3px;
      background: #e0e0e0;
      border-radius: 2px;
      overflow: hidden;
    }
    
    .strength-meter {
      height: 100%;
      width: 0%;
      transition: width 0.3s ease;
    }
    
    .strength-weak {
      background: var(--error);
    }
    
    .strength-medium {
      background: var(--warning);
    }
    
    .strength-strong {
      background: var(--success);
    }
    
    .password-hint {
      font-size: 0.75rem;
      color: var(--gray);
      margin-top: 0.2rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    
    .submit-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 0.8rem 1.5rem;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.2s ease;
      cursor: pointer;
      border: none;
      background: var(--primary);
      color: white;
      margin-top: 0.5rem;
      width: 100%;
    }
    
    .submit-btn:hover {
      background: var(--secondary);
    }
    
    .alert {
      padding: 0.8rem;
      border-radius: 6px;
      margin: 1rem 0;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-size: 0.9rem;
    }
    
    .alert-success {
      background: rgba(76, 201, 240, 0.1);
      color: var(--success);
      border-left: 4px solid var(--success);
    }
    
    .alert-error {
      background: rgba(244, 67, 54, 0.1);
      color: var(--error);
      border-left: 4px solid var(--error);
    }
    
    .alert i {
      font-size: 1.1rem;
    }
    
    h3 {
      margin-bottom: 1.25rem;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-size: 1.1rem;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Extra small devices (phones, 360px and down) */
    @media (max-width: 360px) {
      header h1 {
        font-size: 1.1rem;
        padding: 0 2.2rem;
      }
      
      .container {
        padding: 0.8rem;
      }
      
      .card {
        padding: 1rem;
      }
      
      .btn-toggle {
        flex: 1 1 100%;
        padding: 0.7rem;
        font-size: 0.85rem;
      }
      
      .form-group {
        margin-bottom: 1rem;
      }
      
      input, select {
        padding: 0.7rem 0.9rem;
      }
      
      .select-wrapper::after {
        right: 10px;
      }
    }
    
    /* Small devices (portrait tablets and large phones, 600px and up) */
    @media (min-width: 600px) {
      header {
        padding: 1.25rem;
      }
      
      header h1 {
        font-size: 1.4rem;
      }
      
      .container {
        padding: 1.5rem;
      }
      
      .card {
        padding: 1.75rem;
      }
      
      .btn-toggle {
        flex: 1 1 auto;
        padding: 0.8rem 1.25rem;
      }
      
      .submit-btn {
        width: auto;
      }
    }
    
    /* Medium devices (landscape tablets, 768px and up) */
    @media (min-width: 768px) {
      header {
        padding: 1.5rem;
      }
      
      header h1 {
        font-size: 1.6rem;
      }
      
      .container {
        padding: 2rem;
      }
      
      .card {
        padding: 2rem;
      }
      
      .btn-toggle {
        font-size: 1rem;
      }
      
      label {
        font-size: 1rem;
      }
      
      input, select {
        font-size: 1rem;
      }
      
      h3 {
        font-size: 1.25rem;
      }
    }
    
    /* Prevent zooming on input focus on mobile */
    @media (max-width: 768px) {
      input, select, textarea {
        font-size: 16px !important;
      }
    }
    
    /* Special styling for iOS devices */
    @supports (-webkit-touch-callout: none) {
      select {
        padding-right: 2rem;
      }
    }
  </style>
</head>
<body>

<header>
  <button onclick="window.location.href='admin_dashboard.php'" class="back-btn" title="Back to Dashboard">
    <i class="fas fa-arrow-left"></i>
  </button>
  <h1>Admin Settings</h1>
</header>

<div class="container">
  <div class="card">
    <div class="btn-group">
      <button class="btn-toggle" onclick="toggleForm('changePasswordForm')">
        <i class="fas fa-key"></i> Change Password
      </button>
      <button class="btn-toggle" onclick="toggleForm('addAdminForm')">
        <i class="fas fa-user-plus"></i> Add Admin
      </button>
    </div>

    <!-- Change Password Form -->
    <div id="changePasswordForm" class="form-section active">
      <h3><i class="fas fa-key"></i> Change Admin Password</h3>
      <?php if (!empty($changePassMsg)): ?>
        <div class="alert alert-<?= $changePassMsgType === 'success' ? 'success' : 'error' ?>">
          <i class="fas <?= $changePassMsgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
          <?= htmlspecialchars($changePassMsg) ?>
        </div>
      <?php endif; ?>
      <form method="post">
        <div class="form-group">
          <label for="username">Admin Username</label>
          <div class="select-wrapper">
            <select id="username" name="username" required>
              <option value="">-- Select Admin --</option>
              <?php foreach ($adminUsers as $user): ?>
                <option value="<?= htmlspecialchars($user) ?>" <?= ($_SESSION['admin'] == $user) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($user) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="old_password">Current Password</label>
          <input type="password" id="old_password" name="old_password" required>
        </div>
        <div class="form-group">
          <label for="new_password">New Password</label>
          <input type="password" id="new_password" name="new_password" required oninput="checkPasswordStrength(this.value)">
          <div class="password-strength">
            <div class="strength-meter" id="strengthMeter"></div>
          </div>
          <div class="password-hint">
            <i class="fas fa-info-circle"></i>
            <span>Use 8+ characters with a mix of letters, numbers & symbols</span>
          </div>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm New Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required oninput="checkPasswordMatch()">
          <div id="passwordMatch" class="password-hint"></div>
        </div>
        <button class="submit-btn" type="submit" name="change_password">
          <i class="fas fa-save"></i> Update Password
        </button>
      </form>
    </div>

    <!-- Add Admin Form -->
    <div id="addAdminForm" class="form-section">
      <h3><i class="fas fa-user-plus"></i> Add New Admin</h3>
      <?php if (!empty($addAdminMsg)): ?>
        <div class="alert alert-<?= $addAdminMsgType === 'success' ? 'success' : 'error' ?>">
          <i class="fas <?= $addAdminMsgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
          <?= htmlspecialchars($addAdminMsg) ?>
        </div>
      <?php endif; ?>
      <form method="post">
        <div class="form-group">
          <label for="new_username">Username</label>
          <input type="text" id="new_username" name="new_username" required>
        </div>
        <div class="form-group">
          <label for="new_password">Password</label>
          <input type="password" id="new_password_add" name="new_password" required oninput="checkPasswordStrength(this.value, 'strengthMeterAdd')">
          <div class="password-strength">
            <div class="strength-meter" id="strengthMeterAdd"></div>
          </div>
          <div class="password-hint">
            <i class="fas fa-info-circle"></i>
            <span>Use 8+ characters with a mix of letters, numbers & symbols</span>
          </div>
        </div>
        <div class="form-group">
          <label for="confirm_password_add">Confirm Password</label>
          <input type="password" id="confirm_password_add" name="confirm_password" required oninput="checkPasswordMatch('new_password_add', 'confirm_password_add', 'passwordMatchAdd')">
          <div id="passwordMatchAdd" class="password-hint"></div>
        </div>
        <button class="submit-btn" type="submit" name="add_admin">
          <i class="fas fa-plus-circle"></i> Create Admin
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  // Show first form by default
  document.addEventListener('DOMContentLoaded', function() {
    toggleForm('changePasswordForm');
  });

  function toggleForm(id) {
    // Hide all forms
    document.querySelectorAll('.form-section').forEach(form => {
      form.classList.remove('active');
    });
    
    // Show selected form
    document.getElementById(id).classList.add('active');
  }

  function checkPasswordStrength(password, meterId = 'strengthMeter') {
    const strengthMeter = document.getElementById(meterId);
    let strength = 0;
    
    // Check password length
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    
    // Check for mixed case
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
    
    // Check for numbers
    if (password.match(/([0-9])/)) strength += 1;
    
    // Check for special chars
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
    
    // Update strength meter
    let width = 0;
    let className = '';
    
    if (strength <= 2) {
      width = 33;
      className = 'strength-weak';
    } else if (strength <= 4) {
      width = 66;
      className = 'strength-medium';
    } else {
      width = 100;
      className = 'strength-strong';
    }
    
    strengthMeter.style.width = width + '%';
    strengthMeter.className = 'strength-meter ' + className;
  }

  function checkPasswordMatch(passwordId = 'new_password', confirmId = 'confirm_password', matchId = 'passwordMatch') {
    const password = document.getElementById(passwordId).value;
    const confirmPassword = document.getElementById(confirmId).value;
    const matchDiv = document.getElementById(matchId);
    
    if (password && confirmPassword) {
      if (password === confirmPassword) {
        matchDiv.innerHTML = '<i class="fas fa-check-circle" style="color:var(--success)"></i> Passwords match';
      } else {
        matchDiv.innerHTML = '<i class="fas fa-exclamation-circle" style="color:var(--error)"></i> Passwords do not match';
      }
    } else {
      matchDiv.innerHTML = '';
    }
  }
</script>

</body>
</html>