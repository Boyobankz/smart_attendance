



<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die("Access denied. This page is for administrators only.");
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($employeeId === "" || $rating === "" || $comment === "") {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO feedback (employee_id, admin_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$employeeId, $_SESSION['user_id'], $rating, $comment]);
        $success = "Feedback submitted.";
    }
}

$employees = $pdo->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$feedbackList = $pdo->query("
    SELECT feedback.rating, feedback.comment, feedback.created_at,
           emp.name AS employee_name, adm.name AS admin_name
    FROM feedback
    JOIN users emp ON feedback.employee_id = emp.id
    JOIN users adm ON feedback.admin_id = adm.id
    ORDER BY feedback.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

function initials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $initials .= strtoupper(substr($p, 0, 1));
    }
    return $initials;
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
<title>Employee Feedback</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrap">

    <div class="dashboard-header">
        <div><h1>Give employee feedback</h1></div>
        <a href="admin.php" class="logout-link">Records</a>
    </div>

    

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: #16a34a; margin-bottom: 14px; font-size: 14px;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="feedback-form-card">
        <form method="POST" action="feedback.php">
            <label for="employee_id">Employee</label>
            <select id="employee_id" name="employee_id" required>
                <option value=""> Select Employee </option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Performance rating</label>
            <div class="rating-picker">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" id="r<?= $i ?>" name="rating" value="<?= $i ?>" required>
                    <label for="r<?= $i ?>"><?= $i ?></label>
                <?php endfor; ?>
            </div>

            <label for="comment">Comment (behavior, time management, etc.)</label>
            <textarea id="comment" name="comment" required placeholder="Behavior, punctuality, time management..."></textarea>

            <button type="submit">Submit feedback</button>
        </form>
    </div>

    <h3 style="font-size: 15px; margin-bottom: 12px; color: #1f2937;">Feedback history</h3>

    <?php if (count($feedbackList) === 0): ?>
        <p style="color: #6b7280; font-size: 14px;">No feedback submitted yet.</p>
    <?php else: ?>
        <?php foreach ($feedbackList as $f): ?>
        <div class="feedback-history-item">
            <div class="feedback-history-header">
                <div class="feedback-employee">
                    <div class="avatar-circle"><?= htmlspecialchars(initials($f['employee_name'])) ?></div>
                    <?= htmlspecialchars($f['employee_name']) ?>
                </div>
                <span class="rating-pill <?= ratingClass($f['rating']) ?>"><?= htmlspecialchars($f['rating']) ?>/5</span>
            </div>
            <div class="feedback-comment"><?= htmlspecialchars($f['comment']) ?></div>
            <div class="feedback-meta">By <?= htmlspecialchars($f['admin_name']) ?> &middot; <?= date('M j, Y', strtotime($f['created_at'])) ?></div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</body>
</html>