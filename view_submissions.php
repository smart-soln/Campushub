<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campushub1');

// Establish database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Database connection error. Please try again later.");
}

// Function to safely execute prepared statements
function executeQuery($sql, $params = [], $types = '') {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    return $stmt;
}

// Function to get department list
function getDepartments() {
    try {
        $stmt = executeQuery("SELECT id, name, code FROM departments ORDER BY name");
        $result = $stmt->get_result();
        $departments = [];
        
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        
        return $departments;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

// Verify admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Get filter parameters
$status = $_GET['status'] ?? 'pending';
$questionId = isset($_GET['question_id']) ? (int)$_GET['question_id'] : null;
$departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;

// Build query
$sql = "SELECT st.*, q.question_text, 
               COALESCE(s.full_name, st.student_name) AS display_name,
               d.name AS department_name, tf.feedback
        FROM student_thoughts st
        JOIN questions q ON st.question_id = q.id
        LEFT JOIN students s ON st.student_id = s.id
        LEFT JOIN departments d ON st.department_id = d.id
        LEFT JOIN thought_feedback tf ON tf.thought_id = st.id
        WHERE st.status = ?";
        
$params = [$status];
$types = "s";

if ($questionId) {
    $sql .= " AND st.question_id = ?";
    $params[] = $questionId;
    $types .= "i";
}

if ($departmentId) {
    $sql .= " AND st.department_id = ?";
    $params[] = $departmentId;
    $types .= "i";
}

$sql .= " ORDER BY st.submitted_at DESC";

// Get submissions
$thoughts = [];
try {
    $stmt = executeQuery($sql, $params, $types);
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $thoughts[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "Error loading submissions";
}

// Get all questions for filter dropdown
$questions = [];
try {
    $result = executeQuery(
        "SELECT id, question_text FROM questions ORDER BY posted_at DESC"
    )->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

// Get all departments for filter dropdown
$departments = getDepartments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Submissions | CampusHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Reuse styles from admin_dashboard.php */
    :root {
      --primary: #3498db;
      --secondary: #2ecc71;
      --dark: #2c3e50;
      --light: #f5f7fa;
      --gray: #95a5a6;
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
    }

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

    /* Main Content */
    .main-content {
      flex: 1;
      padding: 20px;
    }

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

    /* Filters */
    .filters {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
      animation: fadeIn 0.5s ease;
    }

    .filter-form {
      display: flex;
      gap: 15px;
      align-items: center;
    }

    .form-group {
      margin-bottom: 0;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
      font-size: 0.9rem;
    }

    select, button {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 0.9rem;
      transition: all 0.3s;
    }

    select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    .btn {
      padding: 8px 15px;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.9rem;
      text-decoration: none;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
      background-color: var(--primary);
      color: white;
      margin-top: 20px;
    }

    .btn:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
    }

    /* Thoughts List */
    .thoughts-list {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
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
      margin-bottom: 10px;
    }

    .thought-question {
      font-style: italic;
      margin-bottom: 10px;
      padding-left: 10px;
      border-left: 3px solid var(--primary);
    }

    .thought-actions {
      display: flex;
      gap: 10px;
    }

    .btn-approve {
      background-color: var(--secondary);
      color: white;
    }

    .btn-approve:hover {
      background-color: #27ae60;
      transform: translateY(-2px);
    }

    .btn-reject {
      background-color: var(--danger);
      color: white;
    }

    .btn-reject:hover {
      background-color: #c0392b;
      transform: translateY(-2px);
    }

    .status-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: 500;
    }

    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-approved {
      background-color: #d4edda;
      color: #155724;
    }

    .status-rejected {
      background-color: #f8d7da;
      color: #721c24;
    }

    .no-results {
      text-align: center;
      padding: 30px;
      color: var(--gray);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .admin-container {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
      }

      .filter-form {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
      </div>
      
      <nav class="sidebar-nav">
        <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="view_submissions.php" class="active"><i class="fas fa-list"></i> View Submissions</a>
        <a href="view_history.php"><i class="fas fa-history"></i> View History</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="topbar">
        <h1><i class="fas fa-list"></i> View Submissions</h1>
        <div class="admin-info">
          Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!
          <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters">
        <form class="filter-form" method="GET">
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
              <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
              <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="question_id">Question</label>
            <select id="question_id" name="question_id">
              <option value="">All Questions</option>
              <?php foreach ($questions as $question): ?>
                <option value="<?= $question['id'] ?>" <?= $questionId === $question['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($question['question_text']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="department_id">Department</label>
            <select id="department_id" name="department_id">
              <option value="">All Departments</option>
              <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>" <?= $departmentId === $dept['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($dept['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <button type="submit" class="btn">
            <i class="fas fa-filter"></i> Filter
          </button>
        </form>
      </div>

      <!-- Thoughts List -->
      <div class="thoughts-list">
        <?php if (!empty($thoughts)): ?>
          <?php foreach ($thoughts as $thought): ?>
            <div class="thought-item">
              <div class="thought-question">
                <strong>Question:</strong> <?= htmlspecialchars($thought['question_text']) ?>
              </div>
              
              <div class="thought-content">
                <?= nl2br(htmlspecialchars($thought['thought_text'])) ?>
              </div>
              
              <div class="thought-meta">
                <span><?= htmlspecialchars($thought['student_name']) ?> (<?= htmlspecialchars($thought['reg_number']) ?>)</span>
                <span><?= htmlspecialchars($thought['department_name']) ?></span>
              </div>
              
              <div class="thought-meta">
                <span>Submitted on <?= date('M d, Y', strtotime($thought['submitted_at'])) ?></span>
                <span class="status-badge status-<?= $thought['status'] ?>">
                  <?= ucfirst($thought['status']) ?>
                </span>
              </div>
              
              <?php if ($thought['status'] === 'pending'): ?>
                <div class="thought-actions">
                  <a href="approve_thought.php?id=<?= $thought['id'] ?>&action=approve" class="btn btn-approve">
                    <i class="fas fa-check"></i> Approve
                  </a>
                  <a href="approve_thought.php?id=<?= $thought['id'] ?>&action=reject" class="btn btn-reject">
                    <i class="fas fa-times"></i> Reject
                  </a>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-results">
            <i class="fas fa-inbox"></i>
            <p>No submissions found with the selected filters</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    // Add animation to thought items
    document.addEventListener('DOMContentLoaded', function() {
      const thoughtItems = document.querySelectorAll('.thought-item');
      
      // Set initial state
      thoughtItems.forEach((item, index) => {
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