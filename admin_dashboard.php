<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - CampusHub</title>
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
      overflow: hidden;
    }

    header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      z-index: 0;
    }

    header h1 {
      position: relative;
      z-index: 1;
      font-size: 1.8rem;
      font-weight: 700;
    }

    .header-icons {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      gap: 12px;
      z-index: 1;
    }

    .header-icons a {
      color: white;
      background: rgba(255, 255, 255, 0.15);
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .header-icons a:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px) scale(1.05);
    }

    .header-icons a i {
      transition: transform 0.3s ease;
    }

    .header-icons a:hover i {
      transform: scale(1.1);
    }

    /* For mobile responsiveness */
    @media (max-width: 768px) {
      .header-icons {
        gap: 8px;
        right: 10px;
      }

      .header-icons a {
        width: 34px;
        height: 34px;
        font-size: 0.8rem;
      }
    }

    .container {
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 1.5rem;
    }

    .card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      border: 1px solid rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      min-height: 200px;
      position: relative;
      overflow: hidden;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
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

    .card h3 {
      font-size: 1.2rem;
      margin-bottom: 1rem;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card p {
      color: var(--gray);
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
      flex-grow: 1;
    }

    .card-icon {
      font-size: 1.5rem;
      color: var(--primary);
    }

    .card-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 0.75rem 1.25rem;
      background: var(--primary);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      align-self: flex-start;
    }

    .card-btn:hover {
      background: var(--secondary);
      transform: translateY(-2px);
    }

    .card-btn i {
      font-size: 0.9rem;
    }

    .welcome-banner {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .welcome-text h2 {
      color: var(--dark);
      margin-bottom: 0.5rem;
      font-size: 1.5rem;
    }

    .welcome-text p {
      color: var(--gray);
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(67, 97, 238, 0.1);
      padding: 0.5rem 1rem;
      border-radius: 50px;
    }

    .user-icon {
      width: 36px;
      height: 36px;
      background: var(--primary);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .username {
      font-weight: 600;
      color: var(--dark);
    }

    @media (max-width: 768px) {
      .dashboard-grid {
        grid-template-columns: 1fr;
      }

      header h1 {
        font-size: 1.5rem;
        padding-right: 50px;
      }

      .header-icons {
        right: 10px;
        gap: 10px;
      }
    }

    /* Animation for cards */
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

    .card {
      animation: fadeInUp 0.5s ease forwards;
      opacity: 0;
    }

    .card:nth-child(1) {
      animation-delay: 0.1s;
    }

    .card:nth-child(2) {
      animation-delay: 0.2s;
    }

    .card:nth-child(3) {
      animation-delay: 0.3s;
    }

    .card:nth-child(4) {
      animation-delay: 0.4s;
    }
    .card:nth-child(5) {
      animation-delay: 0.5s;
    }
  </style>
</head>

<body>

  <header>
    <h1>CampusHub Admin Dashboard</h1>
    <div class="header-icons">
      <a href="admin_profile.php" title="Profile"><i class="fas fa-user-cog"></i></a>
      <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </header>

  <div class="container">
    <div class="welcome-banner">
      <div class="welcome-text">
        <h2>Welcome back, Administrator!</h2>
        <p>Manage your campus Q&A system efficiently</p>
      </div>
      <div class="user-info">
        <div class="user-icon">
          <i class="fas fa-user-shield"></i>
        </div>
        <span class="username"><?php echo htmlspecialchars($_SESSION['admin']); ?></span>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="card">
        <h3><i class="fas fa-edit card-icon"></i> Post a New Question</h3>
        <p>Create and publish new questions for students to respond to.</p>
        <a href="post_question.php" class="card-btn">
          <i class="fas fa-plus"></i> Post Question
        </a>
      </div>

      <div class="card">
        <h3><i class="fas fa-tasks card-icon"></i> Manage Questions</h3>
        <p>Edit, update or remove existing questions from the system.</p>
        <a href="manage_questions.php" class="card-btn">
          <i class="fas fa-pencil-alt"></i> Edit Questions
        </a>
      </div>

      <div class="card">
        <h3><i class="fas fa-inbox card-icon"></i> View Submissions</h3>
        <p>Review and manage student responses and submissions.</p>
        <a href="view_submissions.php" class="card-btn">
          <i class="fas fa-eye"></i> View Responses
        </a>
      </div>

      <div class="card">
        <h3><i class="fas fa-history card-icon"></i> Questions History</h3>
        <p>Access archive of past questions and student interactions.</p>
        <a href="view_history.php" class="card-btn">
          <i class="fas fa-clock"></i> View Archive
        </a>
      </div>

      <div class="card">
        <h3><i class="fas fa-check-double card-icon"></i> Manage Submissions</h3>
        <p>Approve or reject student submissions and manage content.</p>
        <a href="manage_thoughts.php" class="card-btn">
          <i class="fas fa-tasks"></i> Manage Content
        </a>
      </div>
    </div>
  </div>

</body>

</html>