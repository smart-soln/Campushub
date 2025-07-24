<?php
// Connect to DB
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $register_no = $_POST['register_no'];
  $student_name = $_POST['student_name'];
  $thought = $_POST['thought'];
  $language = $_POST['language'];

  // Get active question
  $q_sql = "SELECT id FROM questions WHERE status = 'active' ORDER BY id DESC LIMIT 1";
  $q_result = $conn->query($q_sql);
  if ($q_result->num_rows > 0) {
    $qid = $q_result->fetch_assoc()['id'];

    $stmt = $conn->prepare("INSERT INTO thoughts (question_id, register_no, student_name, thought, language, status, submitted_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("issss", $qid, $register_no, $student_name, $thought, $language);
    if ($stmt->execute()) {
      $success = "Your thought has been submitted for approval!";
    } else {
      $success = "Something went wrong. Try again.";
    }
    $stmt->close();
  } else {
    $success = "No active question available.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Answer | CampusHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --secondary: #10b981;
      --accent: #f43f5e;
      --light: #f8fafc;
      --dark: #0f172a;
      --gray: #64748b;
      --gray-light: #e2e8f0;
      --card-bg: #ffffff;
      --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
      color: var(--dark);
      line-height: 1.6;
      min-height: 100vh;
    }
    
    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 1rem;
    }
    
    /* Header Styles */
    header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 1.5rem;
      text-align: center;
      box-shadow: var(--shadow-lg);
      position: relative;
      margin-bottom: 2rem;
    }
    
    header h1 {
      font-size: 1.5rem;
      font-weight: 600;
    }
    
    .back-btn {
      position: absolute;
      left: 1rem;
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
    
    /* Form Styles */
    .form-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 2rem;
      box-shadow: var(--shadow-lg);
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
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
    
    .form-title {
      text-align: center;
      margin-bottom: 1.5rem;
      color: var(--primary-dark);
      font-size: 1.5rem;
      font-weight: 600;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--dark);
    }
    
    .form-input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid var(--gray-light);
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.2s ease;
      background-color: white;
    }
    
    .form-input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .form-textarea {
      min-height: 150px;
      resize: vertical;
    }
    
    .form-select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 1rem center;
      background-size: 16px 12px;
    }
    
    .submit-btn {
      width: 100%;
      padding: 0.875rem;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
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
      gap: 0.5rem;
      margin-top: 0.5rem;
      box-shadow: var(--shadow);
    }
    
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }
    
    .message {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 500;
      animation: fadeIn 0.5s ease;
    }
    
    .success {
      background-color: rgba(16, 185, 129, 0.1);
      color: var(--secondary);
      border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    .error {
      background-color: rgba(239, 68, 68, 0.1);
      color: var(--accent);
      border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
      .container {
        padding: 0.5rem;
      }
      
      header {
        padding: 1rem;
      }
      
      header h1 {
        font-size: 1.25rem;
        padding: 0 2.5rem;
      }
      
      .back-btn {
        width: 32px;
        height: 32px;
        left: 0.5rem;
      }
      
      .form-card {
        padding: 1.5rem;
      }
    }
    
    @media (max-width: 480px) {
      .form-card {
        padding: 1.25rem;
      }
      
      .form-title {
        font-size: 1.25rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <a href="index.php" class="back-btn" title="Back to Home">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h1>Submit Your Thought</h1>
  </header>

  <div class="container">
    <div class="form-card">
      <?php if ($success): ?>
        <div class="message <?= strpos($success, 'wrong') ? 'error' : 'success' ?>">
          <i class="fas <?= strpos($success, 'wrong') ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
          <?= $success ?>
        </div>
      <?php endif; ?>

      <h2 class="form-title">Share Your Perspective</h2>
      
      <form method="POST" action="">
        <div class="form-group">
          <label for="register_no" class="form-label">Register Number</label>
          <input type="text" name="register_no" id="register_no" class="form-input" required>
        </div>
        
        <div class="form-group">
          <label for="student_name" class="form-label">Student Name</label>
          <input type="text" name="student_name" id="student_name" class="form-input" required>
        </div>
        
        <div class="form-group">
          <label for="thought" class="form-label">Your Answer / Thought</label>
          <textarea name="thought" id="thought" class="form-input form-textarea" required></textarea>
        </div>
        
        <div class="form-group">
          <label for="language" class="form-label">Language</label>
          <select name="language" id="language" class="form-input form-select" required>
            <option value="en">English</option>
            <option value="ta">தமிழ் (Tamil)</option>
          </select>
        </div>
        
        <button type="submit" class="submit-btn">
          <i class="fas fa-paper-plane"></i> Submit for Review
        </button>
      </form>
    </div>
  </div>
</body>
</html>