<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM questions WHERE id=$id");
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $question_en = $_POST['question_en'];
  $question_ta = $_POST['question_ta'];
  $status = $_POST['status'];

  $stmt = $conn->prepare("UPDATE questions SET question_en=?, question_ta=?, status=? WHERE id=?");
  $stmt->bind_param("sssi", $question_en, $question_ta, $status, $id);
  $stmt->execute();

  header("Location: manage_questions.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Question - CampusHub</title>
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
      max-width: 800px;
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
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--dark);
    }
    
    input, textarea, select {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      font-family: inherit;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
    }
    
    textarea {
      min-height: 120px;
      resize: vertical;
    }
    
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      font-size: 1rem;
      transition: all 0.3s ease;
      cursor: pointer;
      border: none;
    }
    
    .btn-primary {
      background: var(--primary);
      color: white;
    }
    
    .btn-primary:hover {
      background: var(--secondary);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-block {
      width: 100%;
    }
    
    .status-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-left: 0.5rem;
    }
    
    .status-active {
      background: rgba(76, 201, 240, 0.1);
      color: var(--success);
    }
    
    .status-inactive {
      background: rgba(247, 37, 133, 0.1);
      color: var(--accent);
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
        padding: 1.5rem;
      }
      
      .back-btn {
        left: 10px;
        width: 32px;
        height: 32px;
      }
    }
    
    @media (max-width: 480px) {
      .container {
        padding: 1rem;
      }
      
      .card {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>

<header>
  <a href="manage_questions.php" class="back-btn" title="Back to Questions">
    <i class="fas fa-arrow-left"></i>
  </a>
  <h1>Edit Question</h1>
</header>

<div class="container">
  <div class="card">
    <form method="POST">
      <div class="form-group">
        <label for="question_en">Question (English)</label>
        <textarea name="question_en" id="question_en" required><?= htmlspecialchars($row['question_en']) ?></textarea>
      </div>
      
      <div class="form-group">
        <label for="question_ta">Question (Tamil)</label>
        <textarea name="question_ta" id="question_ta" required><?= htmlspecialchars($row['question_ta']) ?></textarea>
      </div>
      
      <div class="form-group">
        <label for="status">Status 
          <span class="status-badge status-<?= $row['status'] === 'active' ? 'active' : 'inactive' ?>">
            <?= ucfirst($row['status']) ?>
          </span>
        </label>
        <select name="status" id="status">
          <option value="active" <?= $row['status'] == 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $row['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-save"></i> Update Question
      </button>
    </form>
  </div>
</div>

</body>
</html>