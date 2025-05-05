<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
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
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    // Get the user's shopping list ID
    $stmt = $pdo->prepare("SELECT list_id FROM ShoppingList WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $list = $stmt->fetch();
    
    if (!$list) {
        echo json_encode(['success' => false, 'error' => 'Shopping list not found']);
        exit;
    }
    
    // Clear all items
    $stmt = $pdo->prepare("DELETE FROM ShoppingListDetails WHERE list_id = ?");
    $stmt->execute([$list['list_id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
} 