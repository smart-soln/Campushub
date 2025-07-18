<?php
require_once 'db_config.php';

$success = false;
$error = '';
$departments = getDepartments();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regNumber = trim($_POST['reg_number']);
    $studentName = trim($_POST['student_name']);
    $department = isset($_POST['department']) ? trim($_POST['department']) : null;
    $thoughtText = trim($_POST['thought_text']);
    
    // Validate input
    if (empty($thoughtText) || strlen($thoughtText) < getSetting('min_thought_length') || 
        strlen($thoughtText) > getSetting('max_thought_length')) {
        $error = "Thought must be between " . getSetting('min_thought_length') . 
                " and " . getSetting('max_thought_length') . " characters";
    } else {
        try {
            // Get current active question
            $result = executeQuery(
                "SELECT id FROM questions WHERE is_active = TRUE ORDER BY posted_at DESC LIMIT 1"
            )->get_result();
            
            if ($result->num_rows > 0) {
                $question = $result->fetch_assoc();
                $questionId = $question['id'];
                
                // Insert thought
                $stmt = executeQuery(
                    "INSERT INTO student_thoughts (
                        question_id, student_name, reg_number, 
                        department, thought_text
                    ) VALUES (?, ?, ?, ?, ?)",
                    [
                        $questionId, $studentName, $regNumber,
                        $department, $thoughtText
                    ],
                    "issss"
                );
                
                $success = true;
            } else {
                $error = "No active question available for submission";
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "Error submitting your thought. Please try again.";
        }
    }
}

// Get current question for display
$currentQuestion = null;
$result = executeQuery(
    "SELECT q.*, a.full_name AS posted_by 
     FROM questions q
     JOIN admin_users a ON q.posted_by = a.id
     WHERE q.is_active = TRUE 
     ORDER BY q.posted_at DESC 
     LIMIT 1"
)->get_result();
$currentQuestion = $result->num_rows > 0 ? $result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Thought | CampusHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #3498db;
      --secondary: #2ecc71;
      --dark: #2c3e50;
      --light: #f5f7fa;
      --gray: #95a5a6;
      --danger: #e74c3c;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9f9;
      color: #333;
    }

    .header {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark);
    }

    .nav-links {
      display: flex;
      list-style: none;
    }

    .nav-links li {
      margin-left: 2rem;
    }

    .nav-links a {
      text-decoration: none;
      color: var(--dark);
      font-weight: 500;
      transition: color 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    /* Form Container */
    .form-container {
      max-width: 800px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      position: relative;
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .back-button {
      position: absolute;
      top: 30px;
      left: 30px;
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .back-button:hover {
      text-decoration: underline;
    }

    .form-container h2 {
      font-size: 1.8rem;
      color: var(--dark);
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .current-question {
      background-color: #f5f7fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }

    .current-question:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .current-question h3 {
      font-size: 1.1rem;
      margin-bottom: 10px;
      color: var(--dark);
    }

    .question-text {
      font-style: italic;
      margin-bottom: 5px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.95rem;
      color: var(--dark);
      font-weight: 500;
    }

    input[type="text"],
    select,
    textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    textarea {
      min-height: 200px;
      resize: vertical;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 24px;
      border-radius: 6px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      gap: 8px;
      font-size: 1rem;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(41, 128, 185, 0.3);
    }

    .success-message {
      background-color: #d4edda;
      color: #155724;
      padding: 12px 15px;
      border-radius: 6px;
      margin-top: 20px;
      font-size: 0.95rem;
      display: <?= $success ? 'block' : 'none' ?>;
      animation: fadeIn 0.5s ease;
    }

    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      padding: 12px 15px;
      border-radius: 6px;
      margin-top: 20px;
      font-size: 0.95rem;
      display: <?= $error ? 'block' : 'none' ?>;
      animation: fadeIn 0.5s ease;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .navbar {
        flex-direction: column;
        padding: 1rem;
      }

      .nav-links {
        margin-top: 1rem;
      }

      .nav-links li {
        margin: 0 0.5rem;
      }

      .form-container {
        padding: 20px;
      }

      .back-button {
        top: 20px;
        left: 20px;
      }
    }

    @media (max-width: 480px) {
      .form-container h2 {
        font-size: 1.5rem;
      }

      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="container">
      <nav class="navbar">
        <div class="logo">CampusHub</div>
        <ul class="nav-links">
          <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
          <li><a href="student_submit.php" class="active"><i class="fas fa-pencil-alt"></i> Submit Thought</a></li>
          <li><a href="view_history.php"><i class="fas fa-history"></i> History</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <div class="container">
    <div class="form-container">
      <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Home
      </a>
      <h2><i class="fas fa-pencil-alt"></i> Submit Your Thought</h2>
      
      <?php if ($currentQuestion): ?>
        <div class="current-question">
          <h3>Today's Question:</h3>
          <p class="question-text"><?= htmlspecialchars($currentQuestion['question_text']) ?></p>
          <div class="question-meta">
            <span>Posted by <?= htmlspecialchars($currentQuestion['posted_by']) ?></span>
          </div>
        </div>
      <?php else: ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i> No active question available for submission
        </div>
      <?php endif; ?>
      
      <form id="thoughtForm" method="POST">
        <div class="form-group">
          <label for="reg_number">Register Number</label>
          <input type="text" id="reg_number" name="reg_number" required placeholder="Enter your registration number">
        </div>

        <div class="form-group">
          <label for="student_name">Student Name</label>
          <input type="text" id="student_name" name="student_name" required placeholder="Enter your full name">
        </div>

        <div class="form-group">
          <label for="department">Department</label>
          <select id="department" name="department" required>
            <option value="">-- Select Department --</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Information Technology">Information Technology</option>
            <option value="Electronics">Electronics</option>
            <option value="Mechanical">Mechanical</option>
            <option value="Civil">Civil</option>
          </select>
        </div>

        <div class="form-group">
          <label for="thought_text">Your Thought</label>
          <textarea id="thought_text" name="thought_text" required placeholder="Write your thoughts about today's question..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" <?= !$currentQuestion ? 'disabled' : '' ?>>
          <i class="fas fa-paper-plane"></i> Submit Thought
        </button>
        
        <div class="success-message" id="successMessage">
          <i class="fas fa-check-circle"></i> Your thought has been submitted successfully! It will be visible after admin approval.
        </div>
        
        <div class="error-message" id="errorMessage">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Handle form submission
    document.getElementById("thoughtForm").addEventListener("submit", function(e) {
      e.preventDefault();
      
      // Simple client-side validation
      const thoughtText = document.getElementById("thought_text").value.trim();
      if (thoughtText.length < 10) {
        document.getElementById("errorMessage").textContent = "Your thought should be at least 10 characters long";
        document.getElementById("errorMessage").style.display = "block";
        return;
      }
      
      // Submit the form if validation passes
      this.submit();
    });

    // Add animation to form elements
    document.addEventListener('DOMContentLoaded', function() {
      const formGroups = document.querySelectorAll('.form-group');
      formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        group.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        
        setTimeout(() => {
          group.style.opacity = '1';
          group.style.transform = 'translateY(0)';
        }, 100);
      });
    });
  </script>
</body>
</html>