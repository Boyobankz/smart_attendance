<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

// Set your workplace coordinates and allowed radius (meters) here
$officeLat = 6.1501;      //  Onitsha coordinates 
$officeLng = 6.8159;
$allowedRadius = 200;     // meters

$data = json_decode(file_get_contents("php://input"), true);
$lat = $data['latitude'] ?? null;
$lng = $data['longitude'] ?? null;

if ($lat === null || $lng === null) {
    echo json_encode(["success" => false, "message" => "Location not provided."]);
    exit;
}

function haversine($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

$distance = haversine($officeLat, $officeLng, $lat, $lng);

if ($distance <= $allowedRadius) {
    $status = "present";
    $stmt = $pdo->prepare("INSERT INTO attendance (user_id, check_in_time, latitude, longitude, status) VALUES (?, NOW(), ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $lat, $lng, $status]);
    echo json_encode(["success" => true, "message" => "Attendance marked successfully. You are within range."]);
} else {
    echo json_encode(["success" => false, "message" => "You are outside the allowed location (distance: " . round($distance) . "m)."]);
}
?>