<?php
session_start();
header('Content-Type: application/json');

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? '';
$id      = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($action !== 'remove' || !$id) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Database connection
$host = 'localhost';
$port = 3307;
$db   = 'project';
$user = 'root';
$pass = '';
$dsn  = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";  // ✅ note the $

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Perform delete
$stmt = $pdo->prepare("DELETE FROM Pantry WHERE pantry_id = :id AND user_id = :uid");
$success = $stmt->execute(['id' => $id, 'uid' => $user_id]);

if ($success && $stmt->rowCount()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Could not remove item']);
}
