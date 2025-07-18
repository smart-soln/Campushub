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

// Get all students
$students = [];
$result = $conn->query("SELECT * FROM students ORDER BY full_name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Get thought counts for each student
$thoughtCounts = [];
$result = $conn->query("SELECT student_id, COUNT(*) as count FROM student_thoughts GROUP BY student_id");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $thoughtCounts[$row['student_id']] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Students | CampusHub Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    /* Add your CSS styles here */
    body { font-family: 'Poppins', sans-serif; }
    .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f5f7fa; }
    tr:hover { background-color: #f5f5f5; }
    .btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; display: inline-block; }
    .btn-edit { background: #3498db; color: white; }
    .btn-delete { background: #e74c3c; color: white; }
    .actions { display: flex; gap: 5px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Manage Students</h1>
    
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Registration Number</th>
          <th>Department</th>
          <th>Email</th>
          <th>Thoughts Submitted</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $student): ?>
          <tr>
            <td><?= htmlspecialchars($student['full_name']) ?></td>
            <td><?= htmlspecialchars($student['reg_number']) ?></td>
            <td><?= htmlspecialchars($student['department']) ?></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
            <td><?= $thoughtCounts[$student['id']] ?? 0 ?></td>
            <td class="actions">
              <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-edit">Edit</a>
              <a href="delete_student.php?id=<?= $student['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    
    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
  </div>
</body>
</html>