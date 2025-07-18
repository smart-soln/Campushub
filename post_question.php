<?php
session_start();

// Verify admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'campushub1';

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error. Please try again later.");
}

// Handle question activation
if (isset($_GET['activate'])) {
    $question_id = intval($_GET['activate']);
    
    try {
        // Deactivate all questions first
        $conn->query("UPDATE questions SET is_active = FALSE");
        
        // Activate the selected question
        $stmt = $conn->prepare("UPDATE questions SET is_active = TRUE WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = "Question activated successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error activating question: " . $e->getMessage();
    }
    
    header("Location: manage_questions.php");
    exit();
}

// Get all questions
$questions = [];
$result = $conn->query("SELECT q.*, a.full_name AS posted_by 
                       FROM questions q
                       JOIN admin_users a ON q.posted_by = a.id
                       ORDER BY q.posted_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Questions | CampusHub Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    /* Add your CSS styles here */
    body { font-family: 'Poppins', sans-serif; }
    .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
    .questions-list { margin-top: 20px; }
    .question-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .question-meta { color: #666; font-size: 0.9rem; margin-bottom: 10px; }
    .question-text { font-size: 1.1rem; margin-bottom: 15px; }
    .btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; display: inline-block; }
    .btn-activate { background: #2ecc71; color: white; }
    .btn-deactivate { background: #e74c3c; color: white; }
    .status-active { color: #2ecc71; font-weight: bold; }
    .status-inactive { color: #666; }
    .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Manage Questions</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
      <div class="message success"><?= $_SESSION['message'] ?></div>
      <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div class="message error"><?= $_SESSION['error'] ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <a href="post_question.php" class="btn btn-activate">Post New Question</a>
    
    <div class="questions-list">
      <?php foreach ($questions as $question): ?>
        <div class="question-card">
          <div class="question-text"><?= htmlspecialchars($question['question_text']) ?></div>
          <div class="question-meta">
            Posted by <?= htmlspecialchars($question['posted_by']) ?> on <?= date('M d, Y', strtotime($question['posted_at'])) ?>
            | Status: <span class="<?= $question['is_active'] ? 'status-active' : 'status-inactive' ?>">
              <?= $question['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </div>
          
          <?php if (!$question['is_active']): ?>
            <a href="manage_questions.php?activate=<?= $question['id'] ?>" class="btn btn-activate">Activate</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    
    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
  </div>
</body>
</html>