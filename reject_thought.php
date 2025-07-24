<?php
// --- DB Connection ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "campus_hub";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("❌ Connection failed: " . $conn->connect_error);
}

// --- Reject Thought Logic ---
if (isset($_GET['id'])) {
  $id = intval($_GET['id']); // Sanitize input

  $sql = "UPDATE thoughts SET status = 'rejected' WHERE id = $id";
  if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Thought rejected ❌'); window.location.href='view_submissions.php';</script>";
  } else {
    echo "<script>alert('Error rejecting thought: " . $conn->error . "'); window.location.href='view_submissions.php';</script>";
  }
} else {
  echo "<script>alert('Invalid request ❗'); window.location.href='view_submissions.php';</script>";
}

$conn->close();
?>
