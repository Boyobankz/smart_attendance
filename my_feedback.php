<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT feedback.rating, feedback.comment, feedback.created_at, adm.name AS admin_name
    FROM feedback
    JOIN users adm ON feedback.admin_id = adm.id
    WHERE feedback.employee_id = ?
    ORDER BY feedback.created_at DESC
");
$stmt->execute([$userId]);
$feedbackList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$avgRating = null;
if (count($feedbackList) > 0) {
    $sum = array_sum(array_column($feedbackList, 'rating'));
    $avgRating = round($sum / count($feedbackList), 1);
}

function ratingClass($rating) {
    if ($rating >= 4) return 'good';
    if ($rating == 3) return 'mid';
    return 'low';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Feedback</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrap">

    <div class="dashboard-header">
        <div><h1>My feedback</h1></div>
        <a href="dashboard.php" class="logout-link">Dashboard</a>
    </div>

    

    <?php if (count($feedbackList) > 0): ?>
    <div class="stats-grid" style="grid-template-columns: repeat(2, minmax(0,1fr));">
        <div class="stat-card">
            <div class="label">Total feedback received</div>
            <div class="value"><?= count($feedbackList) ?></div>
        </div>
        <div class="stat-card">
            <div class="label">Average rating</div>
            <div class="value <?= $avgRating >= 4 ? 'success' : ($avgRating < 3 ? 'warning' : '') ?>"><?= $avgRating ?>/5</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($feedbackList) === 0): ?>
        <div class="feedback-form-card" style="text-align: center; color: #6b7280;">
            You haven't received any feedback yet.
        </div>
    <?php else: ?>
        <?php foreach ($feedbackList as $f): ?>
        <div class="feedback-history-item">
            <div class="feedback-history-header">
                <span style="font-size: 13px; color: #6b7280;">Feedback from <?= htmlspecialchars($f['admin_name']) ?></span>
                <span class="rating-pill <?= ratingClass($f['rating']) ?>"><?= htmlspecialchars($f['rating']) ?>/5</span>
            </div>
            <div class="feedback-comment"><?= htmlspecialchars($f['comment']) ?></div>
            <div class="feedback-meta"><?= date('M j, Y', strtotime($f['created_at'])) ?></div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</body>
</html>