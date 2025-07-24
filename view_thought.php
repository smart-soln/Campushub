<?php
// 🔌 DB Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "campus_hub";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("❌ Connection failed: " . $conn->connect_error);
}

// ✅ Fetch approved thoughts with their questions
$sql = "SELECT t.*, q.question_en, q.question_ta
        FROM thoughts t
        JOIN questions q ON t.question_id = q.id
        WHERE t.status = 'approved'
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Thoughts | Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f7f9fc;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 960px;
      margin: auto;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
    }
    .card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .question {
      font-weight: bold;
      margin-bottom: 8px;
    }
    .answer {
      background-color: #eef6ff;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
    }
    .meta {
      font-size: 0.9em;
      color: #555;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>✅ Approved Thoughts</h2>

    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card">
          <div class="question">
            Q: <?= htmlspecialchars($row['question_en']) ?><br>
            <em><?= htmlspecialchars($row['question_ta']) ?></em>
          </div>
          <div class="answer">
            <?= nl2br(htmlspecialchars($row['answer'])) ?>
          </div>
          <div class="meta">
            By <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['regno']) ?>, <?= htmlspecialchars($row['department']) ?>) — <?= strtoupper($row['language']) ?> | <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center;">No approved thoughts yet 👀</p>
    <?php endif; ?>
  </div>
</body>
</html>
