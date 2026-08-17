


<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Attendance System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrap">

    <div class="dashboard-header">
        <div>
            <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
            <div class="subtitle"><?= date('l, F j, Y') ?></div>
        </div>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="status-banner">
        <p id="status">Click below to mark your attendance.</p>
    </div>

    <div class="action-buttons">
        <button id="markBtn">Check In</button>
        <button id="checkoutBtn">Check Out</button>
    </div>

    <div class="links-card">
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="admin.php">View Attendance Records</a>
            <a href="feedback.php">Give Employee Feedback</a>
        <?php endif; ?>
        <a href="my_feedback.php" >My Feedback</a>
    </div>

</div>
<script src="script.js"></script>
</body>
</html>