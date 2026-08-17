


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

// Records
$stmt = $pdo->query("
    SELECT attendance.id, users.name, attendance.check_in_time, attendance.check_out_time,
           attendance.latitude, attendance.longitude, attendance.status
    FROM attendance
    JOIN users ON attendance.user_id = users.id
    WHERE DATE(attendance.check_in_time) = CURDATE()
    ORDER BY attendance.check_in_time DESC
");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalCheckIns = $pdo->query("SELECT COUNT(*) FROM attendance")->fetchColumn();

$presentToday = $pdo->query("
    SELECT COUNT(*) FROM attendance WHERE DATE(check_in_time) = CURDATE()
")->fetchColumn();

$stillCheckedIn = $pdo->query("
    SELECT COUNT(*) FROM attendance WHERE check_out_time IS NULL
")->fetchColumn();

// Helper to build initials from a name
function initials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $initials .= strtoupper(substr($p, 0, 1));
    }
    return $initials;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Records</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrap">

    <div class="dashboard-header">
        <div>
            <h1>Attendance records</h1>
        </div>
        <a href="dashboard.php" class="logout-link">Dashboard</a>
    </div>

    <div class="top-links">
    <a href="dashboard.php" class="logout-link">Back to dashboard</a> &middot; 
    <a href="feedback.php" class="logout-link">Give feedback</a> &middot;
     <a href="history.php" class="logout-link">View past records</a>
</div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total check-ins</div>
            <div class="value"><?= (int)$totalCheckIns ?></div>
        </div>
        <div class="stat-card">
            <div class="label">Present today</div>
            <div class="value success"><?= (int)$presentToday ?></div>
        </div>
        <div class="stat-card">
            <div class="label">Still checked in</div>
            <div class="value warning"><?= (int)$stillCheckedIn ?></div>
        </div>
    </div>

    <div class="records-table-wrap">
        <table>
            <tr>
                <th>Employee</th>
                <th>Check in</th>
                <th>Check out</th>
                <th>Location</th>
                <th>Status</th>
            </tr>
            <?php if (count($records) === 0): ?>
                <tr><td colspan="5">No check-ins yet today.</td></tr>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td>
                        <div class="employee-cell">
                            <div class="avatar-circle"><?= htmlspecialchars(initials($r['name'])) ?></div>
                            <?= htmlspecialchars($r['name']) ?>
                        </div>
                    </td>
                    <td><?= date('g:i A', strtotime($r['check_in_time'])) ?></td>
                    <td>
                        <?php if ($r['check_out_time']): ?>
                            <?= date('g:i A', strtotime($r['check_out_time'])) ?>
                        <?php else: ?>
                            <span class="muted-text">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td class="muted-text"><?= htmlspecialchars($r['latitude']) ?>, <?= htmlspecialchars($r['longitude']) ?></td>
                    <td>
                        <?php if ($r['check_out_time']): ?>
                            <span class="status-pill present">Present</span>
                        <?php else: ?>
                            <span class="status-pill checked-in">Checked in</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

</div>
</body>
</html>