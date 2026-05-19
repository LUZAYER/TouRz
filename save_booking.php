<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$destination = $_POST['destination'] ?? '';
$team_size = intval($_POST['team_size'] ?? 0);
$total_price = floatval(str_replace('/-', '', $_POST['total_price'] ?? '0'));

if (!$name || !$email || !$phone || !$destination || $team_size <= 0 || $total_price <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO bookings (user_id, name, email, phone, destination, team_size, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssid", $user_id, $name, $email, $phone, $destination, $team_size, $total_price);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Booking saved', 'booking_id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save booking']);
}

$stmt->close();
$conn->close();
