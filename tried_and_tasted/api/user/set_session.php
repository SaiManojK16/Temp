<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID missing']);
    exit;
}

$_SESSION['user_id'] = $data['user_id'];

echo json_encode(['success' => true, 'message' => 'Session set']);
