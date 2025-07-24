<?php
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT t.student_name, t.thought, t.language, t.submitted_at, q.question_en, q.question_ta 
        FROM thoughts t 
        JOIN questions q ON t.question_id = q.id 
        WHERE t.status = 'approved' 
        ORDER BY t.submitted_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Thoughts History | CampusHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      max-width: 800px;
      margin: 0 auto;
    }
    
    /* Entries Grid */
    .entries-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
      margin-top: 1.5rem;
    }
    
    .entry-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      border-left: 4px solid var(--success);
      animation: fadeInUp 0.5s ease forwards;
      opacity: 0;
    }
    
    .entry-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }
    
    .question {
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--primary);
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
    }
    
    .question::before {
      content: "Q:";
      color: var(--primary);
      font-weight: bold;
    }
    
    .thought-content {
      background: var(--light);
      padding: 1rem;
      border-radius: 8px;
      margin: 1rem 0;
      font-size: 0.95rem;
      line-height: 1.6;
      white-space: pre-wrap;
      border-left: 3px solid var(--primary-light);
    }
    
    .meta-info {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      font-size: 0.85rem;
      color: var(--gray);
      margin-top: 1rem;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .meta-item i {
      color: var(--primary);
    }
    
    .language-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      background: var(--primary-light);
      color: white;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
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
    
    /* Animation */
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
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
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
      
      .entry-card {
        padding: 1.25rem;
      }
    }
    
    @media (max-width: 480px) {
      .meta-info {
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <a href="javascript:history.back()" class="back-btn" title="Go Back">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h1>Approved Thoughts History</h1>
  </header>

  <div class="container">
    <?php if ($result->num_rows > 0): ?>
      <div class="entries-grid">
        <?php 
          $rowIndex = 0;
          while ($row = $result->fetch_assoc()): 
            $question = $row['language'] === 'ta' ? $row['question_ta'] : $row['question_en'];
            $langLabel = $row['language'] === 'ta' ? 'Tamil' : 'English';
            $submittedDate = date('M d, Y h:i A', strtotime($row['submitted_at']));
        ?>
          <div class="entry-card" style="animation-delay: <?= 0.1 + ($rowIndex / 10) ?>s">
            <div class="question">
              <?= htmlspecialchars($question) ?>
              <span class="language-badge"><?= $langLabel ?></span>
            </div>
            
            <div class="thought-content">
              <?= nl2br(htmlspecialchars($row['thought'])) ?>
            </div>
            
            <div class="meta-info">
              <div class="meta-item">
                <i class="fas fa-user"></i>
                <span><?= htmlspecialchars($row['student_name']) ?></span>
              </div>
              
              <div class="meta-item">
                <i class="fas fa-calendar-alt"></i>
                <span><?= $submittedDate ?></span>
              </div>
            </div>
          </div>
        <?php 
          $rowIndex++;
          endwhile; 
        ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="far fa-check-circle"></i>
        <p>No approved thoughts available yet</p>
        <p>Check back later!</p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
<?php $conn->close(); ?>