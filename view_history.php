<?php
session_start();
require_once 'db_config.php';

$isAdmin = isset($_SESSION['admin_logged_in']);
$questionId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get specific question or list of questions
if ($questionId) {
    try {
        // Get question details
        $stmt = executeQuery(
            "SELECT q.*, a.full_name AS posted_by, d.name AS department_name
             FROM questions q
             JOIN admin_users a ON q.posted_by = a.id
             LEFT JOIN departments d ON a.department_id = d.id
             WHERE q.id = ?",
            [$questionId],
            'i'
        );
        $currentQuestion = $stmt->get_result()->fetch_assoc();
        
        // Get approved thoughts for this question
        $stmt = executeQuery(
            "SELECT st.*, COALESCE(s.full_name, st.student_name) AS display_name,
                    d.name AS department_name, tf.feedback
             FROM student_thoughts st
             LEFT JOIN students s ON st.student_id = s.id
             LEFT JOIN departments d ON st.department_id = d.id
             LEFT JOIN thought_feedback tf ON tf.thought_id = st.id
             WHERE st.question_id = ? AND st.status = 'approved'
             ORDER BY st.submitted_at DESC",
            [$questionId],
            'i'
        );
        $thoughts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = "Error loading question data";
    }
} else {
    // Get all past questions
    try {
        $result = executeQuery(
            "SELECT q.id, q.question_text, q.posted_at, 
                    a.full_name AS posted_by, d.name AS department_name
             FROM questions q
             JOIN admin_users a ON q.posted_by = a.id
             LEFT JOIN departments d ON a.department_id = d.id
             WHERE q.is_active = FALSE
             ORDER BY q.posted_at DESC"
        )->get_result();
        
        $pastQuestions = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = "Error loading past questions";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $questionId ? 'Question History' : 'View History' ?> | CampusHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Reuse styles from index.php and admin_dashboard.php */
    :root {
      --primary: #3498db;
      --secondary: #2ecc71;
      --dark: #2c3e50;
      --light: #f5f7fa;
      --gray: #95a5a6;
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

    <?php if ($isAdmin): ?>
    .admin-container {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background-color: var(--dark);
      color: white;
      padding: 20px;
    }

    .sidebar-header {
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 30px;
    }

    .sidebar-header h2 {
      font-size: 1.3rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sidebar-nav {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      transition: all 0.3s;
    }

    .sidebar-nav a:hover, .sidebar-nav a.active {
      background-color: var(--primary);
    }

    .sidebar-nav a i {
      width: 20px;
      text-align: center;
    }
    <?php endif; ?>

    /* Main Content */
    .main-content {
      <?= $isAdmin ? 'flex: 1;' : '' ?>
      padding: 20px;
    }

    <?php if ($isAdmin): ?>
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 25px;
    }

    .topbar h1 {
      font-size: 1.4rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .admin-info {
      font-weight: 500;
    }

    .logout-btn {
      color: var(--danger);
      text-decoration: none;
      font-weight: 500;
      margin-left: 20px;
    }

    .logout-btn:hover {
      text-decoration: underline;
    }
    <?php else: ?>
    .header {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
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
    <?php endif; ?>

    /* History Content */
    .history-container {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      animation: fadeIn 0.5s ease;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    h2 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Question List */
    .questions-list {
      display: grid;
      gap: 15px;
    }

    .question-item {
      padding: 15px;
      border: 1px solid #eee;
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .question-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .question-item h3 {
      margin-bottom: 5px;
    }

    .question-meta {
      display: flex;
      justify-content: space-between;
      color: var(--gray);
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .btn {
      display: inline-block;
      padding: 8px 15px;
      border-radius: 6px;
      background-color: var(--primary);
      color: white;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      margin-top: 10px;
      transition: all 0.3s;
    }

    .btn:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
    }

    /* Thought List */
    .thoughts-list {
      margin-top: 20px;
    }

    .thought-item {
      padding: 15px;
      border: 1px solid #eee;
      border-radius: 6px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
    }

    .thought-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .thought-content {
      margin-bottom: 10px;
      line-height: 1.6;
    }

    .thought-meta {
      display: flex;
      justify-content: space-between;
      font-size: 0.85rem;
      color: var(--gray);
    }

    .no-results {
      text-align: center;
      padding: 30px;
      color: var(--gray);
    }

    /* Responsive */
    @media (max-width: 768px) {
      <?php if ($isAdmin): ?>
      .admin-container {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
      }
      <?php endif; ?>
    }
  </style>
</head>
<body>
  <?php if ($isAdmin): ?>
  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
      </div>
      
      <nav class="sidebar-nav">
        <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="view_submissions.php"><i class="fas fa-list"></i> View Submissions</a>
        <a href="view_history.php" class="active"><i class="fas fa-history"></i> View History</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="topbar">
        <h1><i class="fas fa-history"></i> View History</h1>
        <div class="admin-info">
          Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!
          <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
  <?php else: ?>
    <header class="header">
      <div class="container">
        <nav class="navbar">
          <div class="logo">CampusHub</div>
          <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="student_submit.php"><i class="fas fa-pencil-alt"></i> Submit Thought</a></li>
            <li><a href="view_history.php"><i class="fas fa-history"></i> History</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <main class="main-content">
      <div class="container">
  <?php endif; ?>

      <div class="history-container">
        <?php if ($questionId && $currentQuestion): ?>
          <a href="view_history.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to All Questions
          </a>
          
          <h2><i class="fas fa-question-circle"></i> <?= htmlspecialchars($currentQuestion['question_text']) ?></h2>
          <div class="question-meta">
            <span>Posted on <?= date('M d, Y', strtotime($currentQuestion['posted_at'])) ?></span>
            <span>Posted by <?= htmlspecialchars($currentQuestion['posted_by']) ?></span>
          </div>
          
          <div class="thoughts-list">
            <?php if (!empty($thoughts)): ?>
              <?php foreach ($thoughts as $thought): ?>
                <div class="thought-item">
                  <div class="thought-content">
                    <?= nl2br(htmlspecialchars($thought['thought_text'])) ?>
                  </div>
                  <div class="thought-meta">
                    <span><?= htmlspecialchars($thought['student_name']) ?> (<?= htmlspecialchars($thought['reg_number']) ?>)</span>
                    <span><?= htmlspecialchars($thought['department_name']) ?></span>
                  </div>
                  <div class="thought-meta">
                    <span>Submitted on <?= date('M d, Y', strtotime($thought['submitted_at'])) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="no-results">
                <i class="fas fa-inbox"></i>
                <p>No approved thoughts for this question</p>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <h2><i class="fas fa-history"></i> Past Questions</h2>
          
          <div class="questions-list">
            <?php if (!empty($pastQuestions)): ?>
              <?php foreach ($pastQuestions as $question): ?>
                <div class="question-item">
                  <h3><?= htmlspecialchars($question['question_text']) ?></h3>
                  <div class="question-meta">
                    <span>Posted by <?= htmlspecialchars($question['posted_by']) ?></span>
                    <span><?= date('M d, Y', strtotime($question['posted_at'])) ?></span>
                  </div>
                  <a href="view_history.php?id=<?= $question['id'] ?>" class="btn">
                    <i class="fas fa-eye"></i> View Responses
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="no-results">
                <i class="fas fa-inbox"></i>
                <p>No past questions available</p>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

  <?php if (!$isAdmin): ?>
      </div>
  <?php endif; ?>
    </main>
  <?php if ($isAdmin): ?>
  </div>
  <?php endif; ?>

  <script>
    // Add animation to question items
    document.addEventListener('DOMContentLoaded', function() {
      const questionItems = document.querySelectorAll('.question-item');
      
      // Set initial state
      questionItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        
        setTimeout(() => {
          item.style.opacity = '1';
          item.style.transform = 'translateY(0)';
        }, 100);
      });
    });
  </script>
</body>
</html>