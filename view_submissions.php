<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// Connect to DB
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get pending thoughts
$sql = "SELECT t.id, t.student_name, t.register_no, t.thought, t.language, t.submitted_at, q.question_en, q.question_ta 
        FROM thoughts t 
        JOIN questions q ON t.question_id = q.id 
        WHERE t.status = 'pending' 
        ORDER BY t.submitted_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending Submissions | CampusHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --accent: #f72585;
      --success: #4cc9f0;
      --danger: #f43f5e;
      --warning: #f59e0b;
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
    
    /* Header */
    header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 1.5rem;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0;
    }
    
    .back-btn {
      position: absolute;
      left: 20px;
      color: white;
      background: rgba(255, 255, 255, 0.2);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .back-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.05);
    }
    
    .container {
      padding: 2rem 1rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      background: var(--card-bg);
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
      margin-top: 2rem;
    }
    
    .empty-state i {
      font-size: 3rem;
      color: var(--gray);
      opacity: 0.5;
      margin-bottom: 1rem;
    }
    
    .empty-state p {
      color: var(--gray);
      font-size: 1.1rem;
    }
    
    /* Submission Cards */
    .submissions-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
      margin-top: 1.5rem;
    }
    
    @media (min-width: 768px) {
      .submissions-grid {
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
      }
    }
    
    .submission-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      border-left: 4px solid var(--warning);
      position: relative;
    }
    
    .submission-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }
    
    .question {
      font-weight: 600;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      color: var(--primary);
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
      font-size: 0.9rem;
      color: var(--gray);
    }
    
    .meta-item i {
      color: var(--primary);
      width: 20px;
      text-align: center;
    }
    
    .thought-content {
      background: rgba(248, 249, 250, 0.8);
      padding: 1rem;
      border-radius: 8px;
      margin: 1rem 0;
      font-size: 0.95rem;
      line-height: 1.6;
      white-space: pre-wrap;
      border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .action-btns {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.75rem 1.25rem;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      flex: 1;
      text-decoration: none;
      border: none;
    }
    
    .btn i {
      font-size: 0.9rem;
    }
    
    .btn-approve {
      background: var(--success);
      color: white;
    }
    
    .btn-approve:hover {
      background: #3aa8d8;
      transform: translateY(-2px);
    }
    
    .btn-reject {
      background: var(--danger);
      color: white;
    }
    
    .btn-reject:hover {
      background: #e23356;
      transform: translateY(-2px);
    }
    
    .language-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      background: var(--primary-light);
      color: white;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 500;
      margin-left: 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 576px) {
      header {
        padding: 1rem;
      }
      
      header h1 {
        font-size: 1.25rem;
        padding: 0 40px;
      }
      
      .back-btn {
        left: 10px;
        width: 36px;
        height: 36px;
      }
      
      .container {
        padding: 1.5rem 1rem;
      }
      
      .action-btns {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
      }
      
      .submission-card {
        padding: 1.25rem;
      }
    }
    
    /* Animation */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .submission-card {
      animation: fadeIn 0.5s ease forwards;
      opacity: 0;
    }
    
    .submission-card:nth-child(1) { animation-delay: 0.1s; }
    .submission-card:nth-child(2) { animation-delay: 0.2s; }
    .submission-card:nth-child(3) { animation-delay: 0.3s; }
    .submission-card:nth-child(4) { animation-delay: 0.4s; }
  </style>
</head>
<body>
  <header>
    <a href="admin_dashboard.php" class="back-btn" title="Back to Dashboard">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h1>Pending Submissions</h1>
  </header>

  <div class="container">
    <?php if ($result->num_rows > 0): ?>
      <div class="submissions-grid">
        <?php while ($row = $result->fetch_assoc()): 
          $question = $row['language'] === 'ta' ? $row['question_ta'] : $row['question_en'];
          $langLabel = $row['language'] === 'ta' ? 'Tamil' : 'English';
        ?>
          <div class="submission-card">
            <div class="question">
              <?= htmlspecialchars($question) ?>
              <span class="language-badge"><?= $langLabel ?></span>
            </div>
            
            <div class="meta-item">
              <i class="fas fa-user"></i>
              <span><?= htmlspecialchars($row['student_name']) ?> (<?= htmlspecialchars($row['register_no']) ?>)</span>
            </div>
            
            <div class="meta-item">
              <i class="fas fa-clock"></i>
              <span><?= date('M d, Y h:i A', strtotime($row['submitted_at'])) ?></span>
            </div>
            
            <div class="thought-content">
              <?= nl2br(htmlspecialchars($row['thought'])) ?>
            </div>
            
            <div class="action-btns">
              <a href="approve_thought.php?id=<?= $row['id'] ?>" class="btn btn-approve">
                <i class="fas fa-check"></i>
                <span>Approve</span>
              </a>
              <a href="reject_thought.php?id=<?= $row['id'] ?>" class="btn btn-reject">
                <i class="fas fa-times"></i>
                <span>Reject</span>
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="far fa-check-circle"></i>
        <p>No pending submissions found</p>
        <p>All caught up!</p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
<?php $conn->close(); ?>