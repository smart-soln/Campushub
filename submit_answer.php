<?php
// 🔌 DB Connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "campus_hub";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("❌ Connection failed: " . $conn->connect_error);
}

$message = "";

// 🔍 Get latest visible question
$question_sql = "SELECT * FROM questions WHERE status = 'visible' ORDER BY created_at DESC LIMIT 1";
$question_result = $conn->query($question_sql);
$question = $question_result->fetch_assoc();

// 💾 Handle submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $regno = trim($_POST["regno"]);
  $name = trim($_POST["name"]);
  $dept = trim($_POST["department"]);
  $answer = trim($_POST["answer"]);
  $language = $_POST["language"] ?? "en";

  if ($regno && $name && $answer) {
    $stmt = $conn->prepare("INSERT INTO thoughts (regno, name, department, answer, language) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $regno, $name, $dept, $answer, $language);
    $stmt->execute();
    $stmt->close();
    $message = "✅ Your answer has been submitted successfully!";
  } else {
    $message = "⚠️ All fields marked with * are required!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Submit Answer | CampusHub</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background-color: #f2f2f2;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin: 10px 0 5px;
    }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      margin-top: 15px;
      padding: 12px 20px;
      background-color: #0066cc;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    .message {
      margin-bottom: 15px;
      font-weight: bold;
      color: green;
    }
    .question-box {
      background: #f4f4f4;
      padding: 15px;
      border-left: 5px solid #0066cc;
      margin-bottom: 20px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>✍️ Submit Your Answer</h2>

    <?php if ($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($question): ?>
      <div class="question-box">
        <strong>Today's Question:</strong><br>
        <?= $question['question_en'] ?><br>
        <?php if (!empty($question['question_ta'])): ?>
          <em>(<?= $question['question_ta'] ?>)</em>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <p>No question posted yet.</p>
    <?php endif; ?>

    <form method="POST" action="">
      <label>Register Number *</label>
      <input type="text" name="regno" required />

      <label>Student Name *</label>
      <input type="text" name="name" required />

      <label>Department</label>
      <input type="text" name="department" />

      <label>Your Answer *</label>
      <textarea name="answer" rows="5" required></textarea>

      <label>Language</label>
      <select name="language">
        <option value="en">English</option>
        <option value="ta">தமிழ்</option>
      </select>

      <button type="submit">Submit</button>
    </form>
  </div>
</body>
</html>
