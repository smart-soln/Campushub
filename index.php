<?php
session_start();

// Database connection
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

// Get today's active question
$currentQuestion = null;
$sql = "SELECT q.id, q.question_text, a.full_name AS posted_by, q.posted_at 
        FROM questions q 
        JOIN admin_users a ON q.posted_by = a.id 
        WHERE q.is_active = TRUE 
        ORDER BY q.posted_at DESC 
        LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $currentQuestion = $result->fetch_assoc();
}

// Get approved thoughts for current question
$approvedThoughts = [];
if ($currentQuestion) {
    $sql = "SELECT st.*, 
                   COALESCE(s.full_name, st.student_name) AS display_name,
                   st.department
            FROM student_thoughts st
            LEFT JOIN students s ON st.student_id = s.id
            WHERE st.question_id = ? 
            AND st.status = 'approved' 
            ORDER BY st.submitted_at DESC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $currentQuestion['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $approvedThoughts[] = $row;
            }
        }
        $stmt->close();
    }
}

// Get past questions (excluding current one)
$pastQuestions = [];
$sql = "SELECT q.id, q.question_text, a.full_name AS posted_by, q.posted_at 
        FROM questions q 
        JOIN admin_users a ON q.posted_by = a.id 
        WHERE q.is_active = FALSE " . ($currentQuestion ? "AND q.id != {$currentQuestion['id']}" : "") . "
        ORDER BY q.posted_at DESC 
        LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pastQuestions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CampusHub | College Interactive Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
      color: var(--dark);
      line-height: 1.6;
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .header {
      background-color: var(--dark);
      color: white;
      padding: 15px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .logo {
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
    }
    
    .nav-links {
      display: flex;
      list-style: none;
    }
    
    .nav-links li {
      margin-left: 20px;
    }
    
    .nav-links a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .nav-links a:hover {
      color: var(--primary);
    }
    
    .main-content {
      padding: 40px 0;
    }
    
    section {
      margin-bottom: 40px;
    }
    
    h2 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .question-card, .thought-card, .question-item {
      background: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .question-text {
      font-size: 1.2rem;
      margin-bottom: 10px;
    }
    
    .question-meta, .thought-meta {
      display: flex;
      justify-content: space-between;
      color: #666;
      font-size: 0.9rem;
    }
    
    .thought-content {
      margin-bottom: 15px;
      line-height: 1.7;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s;
      gap: 8px;
    }
    
    .primary-btn {
      background-color: var(--primary);
      color: white;
    }
    
    .primary-btn:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
    }
    
    .secondary-btn {
      background-color: var(--secondary);
      color: white;
    }
    
    .outline-btn {
      border: 1px solid var(--primary);
      color: var(--primary);
    }
    
    .no-thoughts, .no-question {
      text-align: center;
      padding: 30px;
      background: white;
      border-radius: 8px;
    }
    
    .questions-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }
    
    .question-item h3 {
      margin-bottom: 10px;
      font-size: 1.1rem;
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
          <li><a href="student_submit.php"><i class="fas fa-pencil-alt"></i> Submit Thought</a></li>
          <li><a href="admin_login.php"><i class="fas fa-user-shield"></i> Admin</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <?php if ($currentQuestion): ?>
        <section class="question-section">
          <h2><i class="fas fa-question-circle"></i> Today's Question</h2>
          <div class="question-card">
            <div class="question-content">
              <p class="question-text"><?= htmlspecialchars($currentQuestion['question_text']) ?></p>
              <div class="question-meta">
                <span>Posted by <?= htmlspecialchars($currentQuestion['posted_by']) ?></span>
                <span><?= date('M d, Y', strtotime($currentQuestion['posted_at'])) ?></span>
              </div>
            </div>
          </div>
        </section>

        <section class="thoughts-section">
          <h2><i class="fas fa-lightbulb"></i> Student Thoughts</h2>
          
          <?php if (!empty($approvedThoughts)): ?>
            <div class="thoughts-list">
              <?php foreach ($approvedThoughts as $thought): ?>
                <div class="thought-card">
                  <div class="thought-content">
                    <?= nl2br(htmlspecialchars($thought['thought_text'])) ?>
                  </div>
                  <div class="thought-meta">
                    <span><?= htmlspecialchars($thought['display_name']) ?> (<?= htmlspecialchars($thought['reg_number']) ?>)</span>
                    <span><?= htmlspecialchars($thought['department']) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="no-thoughts">No approved thoughts yet. Be the first to submit!</p>
          <?php endif; ?>
          
          <a href="student_submit.php" class="btn primary-btn">
            <i class="fas fa-pencil-alt"></i> Submit Your Thought
          </a>
        </section>
      <?php else: ?>
        <section class="no-question">
          <h2>No active question today</h2>
          <p>Check back later or login as admin to post a new question.</p>
        </section>
      <?php endif; ?>

      <?php if (!empty($pastQuestions)): ?>
        <section class="past-questions">
          <h2><i class="fas fa-history"></i> Past Questions</h2>
          <div class="questions-list">
            <?php foreach ($pastQuestions as $question): ?>
              <div class="question-item">
                <h3><?= htmlspecialchars($question['question_text']) ?></h3>
                <div class="question-meta">
                  <span>Posted by <?= htmlspecialchars($question['posted_by']) ?></span>
                  <span><?= date('M d, Y', strtotime($question['posted_at'])) ?></span>
                </div>
                <a href="view_history.php?id=<?= $question['id'] ?>" class="btn outline-btn">
                  View Responses
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="view_history.php" class="btn secondary-btn">
            View All History <i class="fas fa-arrow-right"></i>
          </a>
        </section>
      <?php endif; ?>
    </div>
  </main>

  <script>
    // Basic JavaScript for interactive elements
    document.addEventListener('DOMContentLoaded', function() {
      // Add any interactive functionality here
      console.log('CampusHub portal loaded');
    });
  </script>
</body>
</html>