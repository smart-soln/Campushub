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

// Get thought details
$thought = null;
if (isset($_GET['id'])) {
    $thought_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT st.*, q.question_text, a.full_name AS admin_name 
                           FROM student_thoughts st
                           LEFT JOIN questions q ON st.question_id = q.id
                           LEFT JOIN admin_users a ON st.reviewed_by = a.id
                           WHERE st.id = ?");
    $stmt->bind_param("i", $thought_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $thought = $result->fetch_assoc();
    $stmt->close();
}

if (!$thought) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Thought | CampusHub Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    /* Add your CSS styles here */
    body { font-family: 'Poppins', sans-serif; }
    .container { max-width: 800px; margin: 0 auto; padding: 20px; }
    .thought-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .meta { color: #666; font-size: 0.9rem; margin-bottom: 15px; }
    .content { line-height: 1.6; margin-bottom: 20px; }
    .btn { padding: 8px 15px; border-radius: 4px; text-decoration: none; display: inline-block; }
    .btn-back { background: #3498db; color: white; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Thought Details</h1>
    
    <div class="thought-card">
      <div class="meta">
        <p>Submitted by: <?= htmlspecialchars($thought['student_name']) ?> (<?= htmlspecialchars($thought['reg_number']) ?>)</p>
        <p>Department: <?= htmlspecialchars($thought['department']) ?></p>
        <p>Submitted at: <?= date('M d, Y h:i A', strtotime($thought['submitted_at'])) ?></p>
        <?php if ($thought['status'] !== 'pending'): ?>
          <p>Status: <?= ucfirst($thought['status']) ?> by <?= htmlspecialchars($thought['admin_name']) ?></p>
          <p>Reviewed at: <?= date('M d, Y h:i A', strtotime($thought['reviewed_at'])) ?></p>
        <?php endif; ?>
      </div>
      
      <div class="content">
        <h3>For question: <?= htmlspecialchars($thought['question_text']) ?></h3>
        <p><?= nl2br(htmlspecialchars($thought['thought_text'])) ?></p>
      </div>
      
      <a href="admin_dashboard.php" class="btn btn-back">Back to Dashboard</a>
    </div>
  </div>
</body>
</html>