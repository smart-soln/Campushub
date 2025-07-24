<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];

  $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
  $stmt->bind_param("ss", $username, $password);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $_SESSION["admin"] = $username;
    $success = "Login successful! Redirecting...";
    // Redirect after showing success message
    header("Refresh: 2; url=admin_dashboard.php");
  } else {
    $error = "Invalid username or password";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login | Campus Hub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --success: #4cc9f0;
      --error: #f72585;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      background: white;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 1rem;
    }
    
    .login-container {
      position: relative;
      width: 100%;
      max-width: 420px;
    }
    
    .login-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 2.5rem;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      backdrop-filter: blur(5px);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .login-box::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
      opacity: 0.1;
      z-index: -1;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .login-header h2 {
      color: var(--dark);
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .login-header p {
      color: var(--gray);
      font-size: 0.9rem;
    }
    
    .login-logo {
      width: 80px;
      height: 80px;
      margin: 0 auto 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary);
      border-radius: 50%;
      color: white;
      font-size: 2rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--dark);
      font-weight: 500;
    }
    
    .form-control {
      width: 100%;
      padding: 12px 16px 12px 40px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background-color: var(--light);
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .input-icon {
      position: absolute;
      left: 12px;
      top: 42px;
      color: var(--gray);
    }
    
    .btn {
      width: 100%;
      padding: 12px;
      background: var(--primary);
      border: none;
      color: white;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    
    .btn:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    .message {
      padding: 12px;
      border-radius: 8px;
      margin-top: 1rem;
      text-align: center;
      font-weight: 500;
      animation: fadeIn 0.5s ease;
    }
    
    .success {
      background-color: rgba(76, 201, 240, 0.2);
      color: var(--success);
      border: 1px solid var(--success);
    }
    
    .error {
      background-color: rgba(247, 37, 133, 0.2);
      color: var(--error);
      border: 1px solid var(--error);
    }
    
    .decoration {
      position: absolute;
      z-index: -1;
      opacity: 0.1;
    }
    
    .decoration-1 {
      top: -50px;
      left: -50px;
      width: 150px;
      height: 150px;
    }
    
    .decoration-2 {
      bottom: -30px;
      right: -30px;
      width: 100px;
      height: 100px;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 480px) {
      .login-box {
        padding: 1.5rem;
      }
      
      .login-header h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- SVG Decorations -->
    <svg class="decoration decoration-1" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <path fill="#4361ee" d="M45.3,-58.2C58.1,-48.2,67.3,-32.8,70.2,-16.3C73.1,0.2,69.7,17.8,59.3,31.9C48.9,46,31.5,56.6,12.2,65.2C-7.1,73.8,-28.3,80.4,-43.6,72.3C-58.9,64.2,-68.3,41.4,-71.7,19.6C-75.1,-2.2,-72.5,-23.1,-61.2,-39.3C-49.9,-55.5,-29.9,-67.1,-10.9,-61.7C8,-56.3,16.1,-34,45.3,-58.2Z" transform="translate(100 100)" />
    </svg>
    
    <svg class="decoration decoration-2" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <path fill="#f72585" d="M38.3,-49.5C50.9,-40.7,63.3,-32.3,68.1,-20.3C72.9,-8.3,70.1,7.3,63.1,21.1C56.1,34.9,45,46.9,31.4,55.3C17.8,63.7,1.8,68.5,-12.8,65.1C-27.4,61.7,-40.5,50.1,-50.7,36.7C-60.9,23.3,-68.1,8.1,-67.4,-7.4C-66.7,-22.9,-58.1,-38.7,-45.2,-47.3C-32.3,-55.9,-15.1,-57.4,-0.6,-56.7C13.8,-56,27.7,-53.1,38.3,-49.5Z" transform="translate(100 100)" />
    </svg>
    
    <div class="login-box">
      <div class="login-header">
        <div class="login-logo">
          <i class="fas fa-user-shield"></i>
        </div>
        <h2>Admin Portal</h2>
        <p>Access your campus management dashboard</p>
      </div>
      
      <form method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <i class="fas fa-user input-icon"></i>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required />
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required />
        </div>
        
        <button type="submit" class="btn">
          <i class="fas fa-sign-in-alt"></i> Login
        </button>
        
        <?php 
          if (isset($success)) {
            echo "<div class='message success'><i class='fas fa-check-circle'></i> $success</div>";
          } 
          if (isset($error)) {
            echo "<div class='message error'><i class='fas fa-exclamation-circle'></i> $error</div>";
          }
        ?>
      </form>
    </div>
  </div>
</body>
</html>