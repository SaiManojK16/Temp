<?php
session_start();
header('Content-Type: application/json');

// Only POST requests
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
$recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;

// Validate parameters
if ($action !== 'delete' || !$recipe_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Database connection
$host = 'localhost';
$port = 3307;
$db   = 'project';
$user = 'root';
$pass = '';
$dsn  = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Perform deletion
$stmt = $pdo->prepare("DELETE FROM Recipes WHERE recipe_id = :rid AND user_id = :uid");
$ok = $stmt->execute(['rid' => $recipe_id, 'uid' => $user_id]);

if ($ok && $stmt->rowCount()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Could not delete recipe']);
}
