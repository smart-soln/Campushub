<?php
session_start();
if (!isset($_SESSION["admin"])) {
  header("Location: admin_login.php");
  exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Delete question
if (isset($_GET["id"])) {
  $id = intval($_GET["id"]);
  $sql = "DELETE FROM questions WHERE id = $id";
  $conn->query($sql);
}

header("Location: manage_questions.php");
exit();
?>
