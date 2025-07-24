CREATE DATABASE IF NOT EXISTS campus_hub;
USE campus_hub;

-- Admin Login Table
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(100) NOT NULL
);

-- Daily Questions Table
CREATE TABLE questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_en TEXT NOT NULL,
  question_ta TEXT NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'inactive',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student Thoughts Table
CREATE TABLE thoughts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  student_name VARCHAR(100) NOT NULL,
  register_no VARCHAR(50) NOT NULL,
  thought TEXT NOT NULL,
  language ENUM('en', 'ta') DEFAULT 'en',
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

INSERT INTO admin (username, password) VALUES ('admin', 'admin123');
