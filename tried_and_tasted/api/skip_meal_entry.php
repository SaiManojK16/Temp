<?php
session_start();
header('Content-Type: application/json');

// DB connection
$host = 'localhost:3307';
$db = 'project';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : null;
if (!$entry_id) {
    echo json_encode(['success' => false, 'error' => 'Missing entry_id']);
    exit;
}

$stmt = $conn->prepare("UPDATE meal_plan_entry SET status='Skipped' WHERE entry_id=?");
$stmt->bind_param('i', $entry_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
} 