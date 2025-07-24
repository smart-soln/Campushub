<?php
// Start session & check if admin is logged in
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $question_en = trim($_POST["question_en"]);
  $question_ta = trim($_POST["question_ta"]);

  if (!empty($question_en) && !empty($question_ta)) {
    $stmt = $conn->prepare("INSERT INTO questions (question_en, question_ta, status) VALUES (?, ?, 'active')");
    $stmt->bind_param("ss", $question_en, $question_ta);
    if ($stmt->execute()) {
      $success_message = 'Question posted successfully!';
    } else {
      $error_message = 'Failed to post question.';
    }
    $stmt->close();
  } else {
    $error_message = 'Please fill in both fields.';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Post Question - CampusHub</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --accent: #f72585;
      --success: #4cc9f0;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --card-bg: rgba(255, 255, 255, 0.95);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    a {
      text-decoration: none;
    }
    
    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      color: var(--dark);
      min-height: 100vh;
    }
    
    header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 1.5rem;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      position: relative;
    }
    
    header h1 {
      font-size: 1.8rem;
      font-weight: 700;
    }
    
    .back-btn {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: white;
      background: rgba(255, 255, 255, 0.2);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .back-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-50%) scale(1.05);
    }
    
    .container {
      padding: 2rem;
      max-width: 800px;
      margin: 0 auto;
    }
    
    .form-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
      border: 1px solid rgba(0, 0, 0, 0.05);
      animation: fadeInUp 0.5s ease forwards;
      position: relative;
    }
    
    .form-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--primary);
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
      display: block;
    }
    
    textarea {
      width: 100%;
      padding: 1rem;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      resize: vertical;
      min-height: 120px;
      font-family: inherit;
      transition: all 0.3s ease;
    }
    
    textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
    
    .submit-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 0.75rem 1.5rem;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 1rem;
    }
    
    .submit-btn:hover {
      background: var(--secondary);
      transform: translateY(-2px);
    }
    
    .btn-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 2rem;
    }
    
    .secondary-btn {
      background: white;
      color: var(--primary);
      border: 1px solid var(--primary);
    }
    
    .secondary-btn:hover {
      background: rgba(67, 97, 238, 0.05);
    }
    
    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: fadeIn 0.3s ease;
    }
    
    .alert-success {
      background: rgba(76, 201, 240, 0.15);
      border: 1px solid var(--success);
      color: var(--dark);
    }
    
    .alert-error {
      background: rgba(247, 37, 133, 0.15);
      border: 1px solid var(--accent);
      color: var(--dark);
    }
    
    .alert i {
      font-size: 1.2rem;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @media (max-width: 768px) {
      header h1 {
        font-size: 1.5rem;
        padding: 0 40px;
      }
      
      .container {
        padding: 1.5rem;
      }
      
      .form-card {
        padding: 1.5rem;
      }
      
      .back-btn {
        left: 10px;
        width: 32px;
        height: 32px;
      }
      
      .btn-container {
        flex-direction: column;
        gap: 1rem;
      }
      
      .btn-container a, 
      .btn-container button {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<header>
  <a href="admin_dashboard.php" class="back-btn" title="Back to Dashboard">
    <i class="fas fa-arrow-left"></i>
  </a>
  <h1>Post a New Question</h1>
</header>

<div class="container">
  <div class="form-card">
    <form method="POST">
      <?php if ($success_message): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($error_message): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
      
      <div class="form-group">
        <label for="question_en"><i class="fas fa-language"></i> Question (English)</label>
        <textarea id="question_en" name="question_en" placeholder="Enter question in English..." required><?php echo isset($_POST['question_en']) ? htmlspecialchars($_POST['question_en']) : ''; ?></textarea>
      </div>
      
      <div class="form-group">
        <label for="question_ta"><i class="fas fa-language"></i> Question (Tamil)</label>
        <textarea id="question_ta" name="question_ta" placeholder="Enter question in Tamil..." required><?php echo isset($_POST['question_ta']) ? htmlspecialchars($_POST['question_ta']) : ''; ?></textarea>
      </div>
      
      <div class="btn-container">
      
        <button type="submit" class="submit-btn">
          <i class="fas fa-paper-plane"></i> Submit Question
        </button>
      </div>
    </form>
  </div>
</div>

</body>
</html>