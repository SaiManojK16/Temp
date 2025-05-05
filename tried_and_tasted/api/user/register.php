<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['full_name'], $data['email'], $data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$full_name = trim($data['full_name']);
$email = trim($data['email']);
$password = password_hash($data['password'], PASSWORD_DEFAULT);

try {
    $check = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Users (full_name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$full_name, $email, $password]);

    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
