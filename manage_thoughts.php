<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "campus_hub");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all thoughts with their questions
$sql = "SELECT t.id, t.student_name, t.register_no, t.thought, t.language, t.status, t.submitted_at,
               q.question_en, q.question_ta
        FROM thoughts t
        JOIN questions q ON t.question_id = q.id
        ORDER BY t.submitted_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Thoughts | CampusHub Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --accent: #f72585;
            --success: #4cc9f0;
            --warning: #f59e0b;
            --danger: #f43f5e;
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .back-btn {
            position: absolute;
            left: 20px;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
        
        .container {
            padding: 2rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            margin-top: 1.5rem;
            overflow-x: auto;
        }
        
        /* Desktop Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        th {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background: rgba(67, 97, 238, 0.03);
        }
        
        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .status-approved {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .status-rejected {
            background: rgba(244, 63, 94, 0.1);
            color: var(--danger);
        }
        
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
            min-width: 80px;
        }
        
        .btn-approve {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .btn-approve:hover {
            background: rgba(76, 201, 240, 0.2);
        }
        
        .btn-reject {
            background: rgba(244, 63, 94, 0.1);
            color: var(--danger);
        }
        
        .btn-reject:hover {
            background: rgba(244, 63, 94, 0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            opacity: 0.5;
            margin-bottom: 1rem;
        }
        
        /* Mobile Card Styles */
        .mobile-card {
            display: none;
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .mobile-card-row {
            display: flex;
            margin-bottom: 0.5rem;
        }
        
        .mobile-card-label {
            font-weight: 600;
            color: var(--primary);
            min-width: 100px;
        }
        
        .mobile-card-value {
            flex: 1;
        }
        
        .thought-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            header {
                padding: 1rem;
            }
            
            header h1 {
                font-size: 1.25rem;
                padding: 0 40px;
            }
            
            .back-btn {
                left: 10px;
                width: 36px;
                height: 36px;
            }
            
            .container {
                padding: 1.5rem 1rem;
            }
            
            .table-container {
                padding: 1rem;
            }
            
            /* Hide desktop table on mobile */
            table {
                display: none;
            }
            
            /* Show mobile cards */
            .mobile-card {
                display: block;
            }
            
            .status {
                padding: 0.3rem 0.8rem;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-card {
                display: none;
            }
            
            table {
                display: table;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="admin_dashboard.php" class="back-btn" title="Back to Dashboard">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1>Manage Student Submissions</h1>
    </header>

    <div class="container">
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <!-- Desktop Table -->
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Question</th>
                            <th>Thought</th>
                            <th>Lang</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sn = 1;
                        while ($row = $result->fetch_assoc()): 
                            $question = $row['language'] === 'ta' ? $row['question_ta'] : $row['question_en'];
                            $statusClass = 'status-' . strtolower($row['status']);
                            $submittedDate = date('M d, Y', strtotime($row['submitted_at']));
                            $shortThought = strlen($row['thought']) > 50 ? substr($row['thought'], 0, 50) . '...' : $row['thought'];
                        ?>
                            <tr>
                                <td><?= $sn++ ?></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['register_no']) ?></td>
                                <td><?= htmlspecialchars($question) ?></td>
                                <td><?= nl2br(htmlspecialchars($shortThought)) ?></td>
                                <td><?= strtoupper($row['language']) ?></td>
                                <td><span class="status <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td><?= $submittedDate ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- Mobile Cards -->
                <?php 
                $result->data_seek(0); // Reset result pointer
                $sn = 1;
                while ($row = $result->fetch_assoc()): 
                    $question = $row['language'] === 'ta' ? $row['question_ta'] : $row['question_en'];
                    $statusClass = 'status-' . strtolower($row['status']);
                    $submittedDate = date('M d, Y', strtotime($row['submitted_at']));
                    $shortThought = strlen($row['thought']) > 100 ? substr($row['thought'], 0, 100) . '...' : $row['thought'];
                ?>
                    <div class="mobile-card">
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">#</div>
                            <div class="mobile-card-value"><?= $sn++ ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Student</div>
                            <div class="mobile-card-value"><?= htmlspecialchars($row['student_name']) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Reg No</div>
                            <div class="mobile-card-value"><?= htmlspecialchars($row['register_no']) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Question</div>
                            <div class="mobile-card-value"><?= htmlspecialchars($question) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Thought</div>
                            <div class="mobile-card-value thought-preview"><?= nl2br(htmlspecialchars($shortThought)) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Language</div>
                            <div class="mobile-card-value"><?= strtoupper($row['language']) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Status</div>
                            <div class="mobile-card-value"><span class="status <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Submitted</div>
                            <div class="mobile-card-value"><?= $submittedDate ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
                
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No student submissions found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>