<?php
session_start();
if (!isset($_SESSION["admin"])) {
  header("Location: admin_login.php");
  exit();
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Approve the thought
if (isset($_GET["id"])) {
  $id = intval($_GET["id"]);
  $sql = "UPDATE thoughts SET status = 'approved' WHERE id = $id";
  $conn->query($sql);
}

header("Location: view_submissions.php");
exit();
?>
