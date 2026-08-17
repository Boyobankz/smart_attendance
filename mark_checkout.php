<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

$userId = $_SESSION['user_id'];

// Find today's check-in that hasn't been checked out yet
$stmt = $pdo->prepare("
    SELECT id FROM attendance
    WHERE user_id = ? AND check_out_time IS NULL
    ORDER BY check_in_time DESC
    LIMIT 1
");
$stmt->execute([$userId]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    echo json_encode(["success" => false, "message" => "No active check-in found. Please check in first."]);
    exit;
}

$stmt = $pdo->prepare("UPDATE attendance SET check_out_time = NOW() WHERE id = ?");
$stmt->execute([$record['id']]);

echo json_encode(["success" => true, "message" => "Checked out successfully."]);
?>