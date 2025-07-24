<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle delete action
if (isset($_GET['delete_id'])) {
  $delete_id = $_GET['delete_id'];
  
  // Show confirmation dialog
  echo "
  <script>
  function confirmDelete() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
    
    document.getElementById('confirmDelete').onclick = function() {
      window.location.href = 'delete_question.php?id=$delete_id';
    }
    
    document.getElementById('cancelDelete').onclick = function() {
      modal.style.display = 'none';
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  }
  
  window.onload = confirmDelete;
  </script>
  ";
}

// Fetch questions
$result = $conn->query("SELECT * FROM questions ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Questions - CampusHub</title>
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
    }
    
    header h1 {
      font-size: 1.8rem;
      font-weight: 700;
    }
    
    .back-btn {
      position: absolute;
      left: 20px;
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
    
    .container {
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
      border: 1px solid rgba(0, 0, 0, 0.05);
      position: relative;
      overflow-x: auto;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--primary);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    
    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    
    tr:hover {
      background: rgba(67, 97, 238, 0.03);
    }
    
    .status {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .status-active {
      background: rgba(76, 201, 240, 0.1);
      color: var(--success);
    }
    
    .status-inactive {
      background: rgba(247, 37, 133, 0.1);
      color: var(--accent);
    }
    
    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 500;
      font-size: 0.85rem;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .edit-btn {
      background: rgba(33, 150, 243, 0.1);
      color: #2196F3;
    }
    
    .edit-btn:hover {
      background: rgba(33, 150, 243, 0.2);
    }
    
    .delete-btn {
      background: rgba(244, 67, 54, 0.1);
      color: #f44336;
    }
    
    .delete-btn:hover {
      background: rgba(244, 67, 54, 0.2);
    }
    
    .btn-group {
      display: flex;
      gap: 0.5rem;
    }
    
    .no-questions {
      text-align: center;
      padding: 2rem;
      color: var(--gray);
    }
    
    /* Delete Confirmation Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    
    .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      text-align: center;
    }
    
    .modal-icon {
      font-size: 3rem;
      color: #f44336;
      margin-bottom: 1rem;
    }
    
    .modal-title {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: var(--dark);
    }
    
    .modal-message {
      color: var(--gray);
      margin-bottom: 2rem;
    }
    
    .modal-buttons {
      display: flex;
      justify-content: center;
      gap: 1rem;
    }
    
    .modal-btn {
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
    }
    
    .modal-btn-cancel {
      background: #f0f0f0;
      color: var(--dark);
    }
    
    .modal-btn-cancel:hover {
      background: #e0e0e0;
    }
    
    .modal-btn-confirm {
      background: #f44336;
      color: white;
    }
    
    .modal-btn-confirm:hover {
      background: #e53935;
    }
    
    @media (max-width: 768px) {
      header h1 {
        font-size: 1.5rem;
        padding: 0 40px;
      }
      
      .container {
        padding: 1.5rem;
      }
      
      .card {
        padding: 1rem;
      }
      
      .back-btn {
        left: 10px;
        width: 32px;
        height: 32px;
      }
      
      table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
      }
      
      th, td {
        padding: 0.75rem;
      }
      
      .btn-group {
        flex-direction: column;
      }
      
      .action-btn {
        width: 100%;
        justify-content: center;
      }
      
      .modal-buttons {
        flex-direction: column;
      }
      
      .modal-btn {
        width: 100%;
      }
    }
    
    @media (max-width: 480px) {
      th, td {
        display: block;
        width: 100%;
      }
      
      tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
      }
      
      td::before {
        content: attr(data-label);
        font-weight: bold;
        display: inline-block;
        width: 120px;
        color: var(--primary);
      }
      
      .btn-group {
        flex-direction: row;
      }
    }
  </style>
</head>
<body>

<header>
  <a href="admin_dashboard.php" class="back-btn" title="Back to Dashboard">
    <i class="fas fa-arrow-left"></i>
  </a>
  <h1>Manage Questions</h1>
</header>

<div class="container">
  <div class="card">
    <?php if ($result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>English</th>
            <th>Tamil</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="ID"><?= $row['id'] ?></td>
              <td data-label="English"><?= htmlspecialchars($row['question_en']) ?></td>
              <td data-label="Tamil"><?= htmlspecialchars($row['question_ta']) ?></td>
              <td data-label="Status">
                <span class="status status-<?= $row['status'] === 'active' ? 'active' : 'inactive' ?>">
                  <?= ucfirst($row['status']) ?>
                </span>
              </td>
              <td data-label="Actions">
                <div class="btn-group">
                  <a href="edit_question.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="manage_questions.php?delete_id=<?= $row['id'] ?>" class="action-btn delete-btn">
                    <i class="fas fa-trash-alt"></i> Delete
                  </a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="no-questions">
        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
        <p>No questions found</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
  <div class="modal-content">
    <div class="modal-icon">
      <i class="fas fa-exclamation-triangle"></i>
    </div>
    <h3 class="modal-title">Confirm Deletion</h3>
    <p class="modal-message">Are you sure you want to delete this question? This action cannot be undone.</p>
    <div class="modal-buttons">
      <button id="cancelDelete" class="modal-btn modal-btn-cancel">Cancel</button>
      <button id="confirmDelete" class="modal-btn modal-btn-confirm">Delete</button>
    </div>
  </div>
</div>

</body>
</html>