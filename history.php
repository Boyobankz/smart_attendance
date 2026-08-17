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

function initials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $initials .= strtoupper(substr($p, 0, 1));
    }
    return $initials;
}

$selectedDate = $_GET['date'] ?? null;

if ($selectedDate) {
    // Show records for the selected past date
    $stmt = $pdo->prepare("
        SELECT attendance.name AS emp_name, attendance.check_in_time, attendance.check_out_time,
               attendance.latitude, attendance.longitude
        FROM (
            SELECT attendance.id, users.name, attendance.check_in_time, attendance.check_out_time,
                   attendance.latitude, attendance.longitude
            FROM attendance
            JOIN users ON attendance.user_id = users.id
        ) AS attendance
        WHERE DATE(attendance.check_in_time) = ?
        ORDER BY attendance.check_in_time DESC
    ");
    $stmt->execute([$selectedDate]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // List all past dates (excluding today) with a record count
    $dates = $pdo->query("
        SELECT DATE(check_in_time) AS the_date, COUNT(*) AS total
        FROM attendance
        WHERE DATE(check_in_time) < CURDATE()
        GROUP BY DATE(check_in_time)
        ORDER BY the_date DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance History</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-wrap">

    <div class="dashboard-header">
        <div><h1><?= $selectedDate ? htmlspecialchars(date('F j, Y', strtotime($selectedDate))) : 'Past records' ?></h1></div>
        <a href="admin.php" class="logout-link">Today's records</a>
    </div>

    <div class="top-links">
        <?php if ($selectedDate): ?>
            <a href="history.php">← All past dates</a> &middot; <a href="admin.php">Today's records</a>
        <?php else: ?>
            <a href="admin.php">← Back to today's records</a>
        <?php endif; ?>
    </div>

    <?php if ($selectedDate): ?>
        <div class="records-table-wrap">
            <table>
                <tr>
                    <th>Employee</th>
                    <th>Check in</th>
                    <th>Check out</th>
                    <th>Location</th>
                </tr>
                <?php if (count($records) === 0): ?>
                    <tr><td colspan="4">No records found for this date.</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td>
                            <div class="employee-cell">
                                <div class="avatar-circle"><?= htmlspecialchars(initials($r['emp_name'])) ?></div>
                                <?= htmlspecialchars($r['emp_name']) ?>
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
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    <?php else: ?>
        <div class="records-table-wrap">
            <table>
                <tr>
                    <th>Date</th>
                    <th>Check-ins</th>
                    <th></th>
                </tr>
                <?php if (count($dates) === 0): ?>
                    <tr><td colspan="3">No past records yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($dates as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('F j, Y', strtotime($d['the_date']))) ?></td>
                        <td><?= (int)$d['total'] ?></td>
                        <td><a href="history.php?date=<?= urlencode($d['the_date']) ?>" style="color:#2563eb; text-decoration:none; font-size:13px;">View →</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    <?php endif; ?>

</div>
</body>
</html>