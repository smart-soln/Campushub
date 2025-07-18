<?php
session_start();

// Database connection (previously in db_connect.php)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'campushub1';

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error. Please try again later.");
}

// Initialize variables
$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    try {
        // Check if admin account exists
        $stmt = $conn->prepare("SELECT id, username, password, full_name FROM admin_users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid password. Please try again.";
                
                // Password reset option for debugging (remove in production)
                if ($username === 'admin' && $password === 'admin123') {
                    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
                    $conn->query("UPDATE admin_users SET password = '$hashed_password' WHERE username = 'admin'");
                    $error .= " Default password was reset. Try again.";
                }
            }
        } else {
            $error = "Username not found";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $error = "Login error. Please try again.";
        error_log("Login error: " . $e->getMessage());
    }
}

// Create default admin if no accounts exist (first-time setup)
$admin_count = $conn->query("SELECT COUNT(*) as count FROM admin_users")->fetch_assoc()['count'];
if ($admin_count == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin_users (username, password, full_name) VALUES ('admin', '$default_password', 'Administrator')");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | CampusHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #3498db;
      --secondary: #2ecc71;
      --dark: #2c3e50;
      --light: #f5f7fa;
      --danger: #e74c3c;
      --warning: #f39c12;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
    
    .login-container {
      background: white;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      padding: 40px;
      animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .login-header h2 {
      color: var(--dark);
      margin-bottom: 10px;
      font-size: 1.8rem;
    }
    
    .login-header p {
      color: #666;
      font-size: 0.9rem;
    }
    
    .login-header i {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 15px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--dark);
      font-size: 0.9rem;
    }
    
    input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      transition: all 0.3s;
    }
    
    input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }
    
    .btn {
      width: 100%;
      padding: 12px;
      background-color: var(--primary);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    
    .btn:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
    }
    
    .error-message {
      color: var(--danger);
      text-align: center;
      margin: 20px 0;
      padding: 12px;
      background-color: rgba(231, 76, 60, 0.1);
      border-radius: 6px;
      display: <?= !empty($error) ? 'flex' : 'none' ?>;
      align-items: center;
      gap: 8px;
      animation: shake 0.5s;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }
    
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: var(--dark);
      text-decoration: none;
      transition: color 0.3s;
      font-size: 0.9rem;
    }
    
    .back-link:hover {
      color: var(--primary);
      text-decoration: underline;
    }
    
    .default-credentials {
      margin-top: 20px;
      padding: 12px;
      background-color: rgba(52, 152, 219, 0.1);
      border-radius: 6px;
      font-size: 0.8rem;
      color: var(--dark);
      text-align: center;
    }

    .password-reset {
      margin-top: 20px;
      font-size: 0.9rem;
      text-align: center;
    }
    .password-reset a {
      color: var(--primary);
      text-decoration: none;
    }
    .password-reset a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <i class="fas fa-user-shield"></i>
      <h2>Admin Login</h2>
      <p>Enter your credentials to access the dashboard</p>
    </div>
    
    <?php if (!empty($error)): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required 
               value="<?= htmlspecialchars($username) ?>">
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      
      <button type="submit" class="btn">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
    </form>
    
    <?php if ($admin_count == 0): ?>
      <div class="default-credentials">
        <p><strong>Default admin account created:</strong></p>
        <p>Username: <code>admin</code></p>
        <p>Password: <code>admin123</code></p>
      </div>
    <?php endif; ?>
  
    
    <a href="index.php" class="back-link">
      <i class="fas fa-arrow-left"></i> Back to Home
    </a>
  </div>
</body>
</html>