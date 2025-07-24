<?php
// Start session and connect to DB
session_start();
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Language switch
$lang = isset($_GET['lang']) && $_GET['lang'] == 'ta' ? 'ta' : 'en';

// Fetch the active question
$question_sql = "SELECT * FROM questions WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$question_result = $conn->query($question_sql);
$question = $question_result->fetch_assoc();

// Fetch approved thoughts
$thoughts = [];
if ($question) {
  $qid = $question['id'];
  $thought_sql = "SELECT * FROM thoughts WHERE question_id = $qid AND status = 'approved' AND language = '$lang' ORDER BY submitted_at DESC";
  $thought_result = $conn->query($thought_sql);
  while ($row = $thought_result->fetch_assoc()) {
    $thoughts[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CampusHub | Student Thoughts Platform</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --secondary: #10b981;
      --accent: #f43f5e;
      --light: #f8fafc;
      --dark: #0f172a;
      --gray: #64748b;
      --gray-light: #e2e8f0;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      line-height: 1.6;
      color: var(--dark);
      background-color: #f8f9fa;
    }
    
    /* Header & Navigation */
    header {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    
    .navbar {
      max-width: 1200px;
      margin: 0 auto;
      padding: 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .logo {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .logo i {
      font-size: 1.75rem;
    }
    
    .nav-links {
      display: flex;
      gap: 1.5rem;
    }
    
    .nav-links a {
      color: var(--dark);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    
    .nav-links a:hover {
      color: var(--primary);
    }
    
    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 4rem 1rem;
      text-align: center;
    }
    
    .hero-content {
      max-width: 800px;
      margin: 0 auto;
    }
    
    .hero h1 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      line-height: 1.2;
    }
    
    .hero p {
      font-size: 1.25rem;
      opacity: 0.9;
      margin-bottom: 2rem;
    }
    
    .hero-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .btn-primary {
      background: white;
      color: var(--primary);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-outline {
      border: 2px solid white;
      color: white;
    }
    
    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px);
    }
    
    /* Main Content */
    .container {
      max-width: 800px;
      margin: 2rem auto;
      padding: 0 1rem;
    }
    
    /* Language Switch */
    .lang-switch {
      text-align: right;
      margin-bottom: 1.5rem;
    }
    
    .lang-btn {
      background: none;
      border: none;
      color: var(--gray);
      font-weight: 500;
      cursor: pointer;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
    }
    
    .lang-btn.active {
      color: var(--primary);
      font-weight: 600;
    }
    
    /* Question Card */
    .question-card {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      border-left: 4px solid var(--primary);
    }
    
    .question-text {
      font-size: 1.25rem;
      font-weight: 500;
      margin-bottom: 1rem;
      line-height: 1.4;
    }
    
    /* Thoughts List */
    .thoughts-list {
      display: grid;
      gap: 1rem;
    }
    
    .thought-card {
      background: white;
      border-radius: 8px;
      padding: 1.5rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      border-left: 3px solid var(--secondary);
    }
    
    .thought-text {
      margin-bottom: 1rem;
      line-height: 1.6;
    }
    
    .thought-meta {
      display: flex;
      justify-content: space-between;
      font-size: 0.875rem;
      color: var(--gray);
    }
    
    .student-info {
      font-weight: 500;
      color: var(--primary-dark);
    }
    
    /* Submit Section */
    .submit-section {
      text-align: center;
      margin-top: 3rem;
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem;
      color: var(--gray);
    }
    
    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: var(--gray-light);
    }
    
    /* Footer */
    footer {
      background: var(--dark);
      color: white;
      padding: 2rem 1rem;
      text-align: center;
      margin-top: 3rem;
    }
    
    .footer-links {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
      margin-bottom: 1rem;
    }
    
    .footer-links a {
      color: white;
      text-decoration: none;
      opacity: 0.8;
      transition: opacity 0.3s;
    }
    
    .footer-links a:hover {
      opacity: 1;
    }
    
    .copyright {
      opacity: 0.6;
      font-size: 0.875rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
      .navbar {
        flex-direction: column;
        gap: 1rem;
      }
      
      .nav-links {
        gap: 1rem;
      }
      
      .hero h1 {
        font-size: 2rem;
      }
      
      .hero p {
        font-size: 1.1rem;
      }
      
      .hero-buttons {
        flex-direction: column;
        align-items: center;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
      
      .question-card {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <nav class="navbar">
      <a href="index.php" class="logo">
        <i class="fas fa-brain"></i>
        <span>CampusHub</span>
      </a>
      <div class="lang-switch">
      <button class="lang-btn <?= $lang == 'en' ? 'active' : '' ?>" onclick="window.location.href='?lang=en'">English</button>
      <button class="lang-btn <?= $lang == 'ta' ? 'active' : '' ?>" onclick="window.location.href='?lang=ta'">தமிழ்</button>
    </div>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1><?= $lang == 'en' ? 'Share Your Thoughts with Campus Community' : 'உங்கள் எண்ணங்களை கேம்பஸ் சமூகத்துடன் பகிர்ந்து கொள்ளுங்கள்' ?></h1>
      <p><?= $lang == 'en' ? 'Engage in meaningful discussions and see what others are thinking' : 'அர்த்தமுள்ள விவாதங்களில் ஈடுபட்டு, மற்றவர்கள் என்ன நினைக்கிறார்கள் என்று பார்க்கவும்' ?></p>
      <div class="hero-buttons">
        <a href="#current-question" class="btn btn-primary">
          <i class="fas fa-question-circle"></i>
          <?= $lang == 'en' ? 'View Question' : 'கேள்வியைக் காண்க' ?>
        </a>
        <a href="student_submit.php" class="btn btn-outline">
          <i class="fas fa-pen-alt"></i>
          <?= $lang == 'en' ? 'Submit Your Answer' : 'உங்கள் பதிலை சமர்ப்பிக்கவும்' ?>
        </a>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <main class="container" id="current-question">
    

    <?php if ($question): ?>
      <div class="question-card">
        <h2><?= $lang == 'en' ? 'Current Question' : 'தற்போதைய கேள்வி' ?></h2>
        <p class="question-text"><?= $lang == 'en' ? htmlspecialchars($question['question_en']) : htmlspecialchars($question['question_ta']) ?></p>
      </div>

      <?php if (count($thoughts) > 0): ?>
        <h2><?= $lang == 'en' ? 'Student Thoughts' : 'மாணவர்களின் எண்ணங்கள்' ?></h2>
        <div class="thoughts-list">
          <?php foreach ($thoughts as $thought): ?>
            <div class="thought-card">
              <p class="thought-text"><?= htmlspecialchars($thought['thought']) ?></p>
              <div class="thought-meta">
                <span class="student-info"><?= htmlspecialchars($thought['student_name']) ?> (<?= htmlspecialchars($thought['register_no']) ?>)</span>
                <span><?= date('d M, Y', strtotime($thought['submitted_at'])) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="far fa-comment-dots"></i>
          <h3><?= $lang == 'en' ? 'No approved thoughts yet' : 'இதுவரை அங்கீகரிக்கப்பட்ட எண்ணங்கள் இல்லை' ?></h3>
          <p><?= $lang == 'en' ? 'Be the first to share your perspective!' : 'உங்கள் பார்வையைப் பகிர முதல் நபராக இருங்கள்!' ?></p>
        </div>
      <?php endif; ?>

      <div class="submit-section">
        <a href="student_submit.php" class="btn btn-primary">
          <i class="fas fa-pen-alt"></i>
          <?= $lang == 'en' ? 'Submit Your Answer' : 'உங்கள் பதிலை சமர்ப்பிக்கவும்' ?>
        </a>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="far fa-question-circle"></i>
        <h3><?= $lang == 'en' ? 'No active question available' : 'செயலில் உள்ள கேள்வி இல்லை' ?></h3>
        <p><?= $lang == 'en' ? 'Please check back later for new questions' : 'புதிய கேள்விகளுக்கு பின்னர் சரிபார்க்கவும்' ?></p>
      </div>
    <?php endif; ?>
  </main>

  
</body>
</html>