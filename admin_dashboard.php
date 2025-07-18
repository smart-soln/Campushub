<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

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

// Get admin details
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Get statistics
$stats = [
    'total_questions' => 0,
    'active_question' => 0,
    'total_thoughts' => 0,
    'pending_thoughts' => 0
];

// Get total questions
$result = $conn->query("SELECT COUNT(*) as count FROM questions");
if ($result) $stats['total_questions'] = $result->fetch_assoc()['count'];

// Get active question
$result = $conn->query("SELECT COUNT(*) as count FROM questions WHERE is_active = TRUE");
if ($result) $stats['active_question'] = $result->fetch_assoc()['count'];

// Get total thoughts
$result = $conn->query("SELECT COUNT(*) as count FROM student_thoughts");
if ($result) $stats['total_thoughts'] = $result->fetch_assoc()['count'];

// Get pending thoughts
$result = $conn->query("SELECT COUNT(*) as count FROM student_thoughts WHERE status = 'pending'");
if ($result) $stats['pending_thoughts'] = $result->fetch_assoc()['count'];

// Get recent pending thoughts
$pendingThoughts = [];
$result = $conn->query("SELECT st.*, q.question_text 
                        FROM student_thoughts st
                        LEFT JOIN questions q ON st.question_id = q.id
                        WHERE st.status = 'pending'
                        ORDER BY st.submitted_at DESC
                        LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pendingThoughts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | CampusHub</title>
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
    a {
        text-decoration: none;
        
    }

    .li {
        list-style: none;
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
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .user-info img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
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
    
    .welcome-section {
      margin-bottom: 40px;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .stat-card {
      background: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card h3 {
      font-size: 1rem;
      color: #666;
      margin-bottom: 10px;
    }
    
    .stat-card .value {
      font-size: 2rem;
      font-weight: 700;
    }
    
    .stat-card.primary .value {
      color: var(--primary);
    }
    
    .stat-card.success .value {
      color: var(--secondary);
    }
    
    .stat-card.warning .value {
      color: var(--warning);
    }
    
    .stat-card.danger .value {
      color: var(--danger);
    }
    
    .section-title {
      font-size: 1.5rem;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .thoughts-list {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .thought-item {
      padding: 15px 20px;
      border-bottom: 1px solid #eee;
    }
    
    .thought-item:last-child {
      border-bottom: none;
    }
    
    .thought-content {
      margin-bottom: 10px;
    }
    
    .thought-meta {
      display: flex;
      justify-content: space-between;
      font-size: 0.9rem;
      color: #666;
    }
    
    .action-buttons {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    
    .btn {
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 0.8rem;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .btn-approve {
      background-color: var(--secondary);
      color: white;
      border: none;
    }
    
    .btn-reject {
      background-color: var(--danger);
      color: white;
      border: none;
    }
    
    .btn-view {
      background-color: var(--primary);
      color: white;
      border: none;
    }
    
    .btn:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="container">
      <nav class="navbar">
        <div class="logo">CampusHub Admin</div>
        <div class="user-info">
          <span>Welcome, <?= htmlspecialchars($admin_name) ?></span>
          <a href="logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <section class="welcome-section">
        <h1>Admin Dashboard</h1>
        <p>Manage questions and student thoughts</p>
      </section>
      
      <section class="stats-section">
        <div class="stats-grid">
          <div class="stat-card primary">
            <h3>Total Questions</h3>
            <div class="value"><?= $stats['total_questions'] ?></div>
          </div>
          
          <div class="stat-card success">
            <h3>Active Question</h3>
            <div class="value"><?= $stats['active_question'] ?></div>
          </div>
          
          <div class="stat-card warning">
            <h3>Total Thoughts</h3>
            <div class="value"><?= $stats['total_thoughts'] ?></div>
          </div>
          
          <div class="stat-card danger">
            <h3>Pending Thoughts</h3>
            <div class="value"><?= $stats['pending_thoughts'] ?></div>
          </div>
        </div>
      </section>
      
      <section class="pending-thoughts">
        <h2 class="section-title"><i class="fas fa-clock"></i> Pending Thoughts</h2>
        
        <?php if (!empty($pendingThoughts)): ?>
          <div class="thoughts-list">
            <?php foreach ($pendingThoughts as $thought): ?>
              <div class="thought-item">
                <div class="thought-content">
                  <p><?= nl2br(htmlspecialchars($thought['thought_text'])) ?></p>
                  <?php if (!empty($thought['question_text'])): ?>
                    <p><small>For question: <?= htmlspecialchars($thought['question_text']) ?></small></p>
                  <?php endif; ?>
                </div>
                <div class="thought-meta">
                  <span>Submitted by: <?= htmlspecialchars($thought['student_name'] ?? 'Anonymous') ?></span>
                  <span><?= date('M d, Y h:i A', strtotime($thought['submitted_at'])) ?></span>
                </div>
                <div class="action-buttons">
                  <a href="approve_thought.php?id=<?= $thought['id'] ?>" class="btn btn-approve">
                    <i class="fas fa-check"></i> Approve
                  </a>
                  <a href="reject_thought.php?id=<?= $thought['id'] ?>" class="btn btn-reject">
                    <i class="fas fa-times"></i> Reject
                  </a>
                  <a href="view_thought.php?id=<?= $thought['id'] ?>" class="btn btn-view">
                    <i class="fas fa-eye"></i> View
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="manage_thoughts.php" class="btn btn-view" style="margin-top: 20px; display: inline-block;">
            <i class="fas fa-list"></i> View All Pending Thoughts
          </a>
        <?php else: ?>
          <p>No pending thoughts to review.</p>
        <?php endif; ?>
      </section>
      
      <section class="quick-actions" style="margin-top: 40px;">
        <h2 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
        <div style="display: flex; gap: 15px;">
          <a href="post_question.php" class="btn btn-approve">
            <i class="fas fa-plus"></i> Post New Question
          </a>
          <a href="manage_questions.php" class="btn btn-view">
            <i class="fas fa-question-circle"></i> Manage Questions
          </a>
          <a href="manage_students.php" class="btn btn-view">
            <i class="fas fa-users"></i> Manage Students
          </a>
        </div>
      </section>
    </div>
  </main>

  <script>
    // Basic JavaScript for interactive elements
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Admin dashboard loaded');
      
      // Add confirmation for actions
      document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function(e) {
          if (!confirm('Are you sure you want to reject this thought?')) {
            e.preventDefault();
          }
        });
      });
    });
  </script>
</body>
</html>