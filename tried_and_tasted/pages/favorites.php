<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
$action = $_POST['action'] ?? '';

$conn = new mysqli("localhost", "root", "", "project", 3307); // ✅ with correct port

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($recipe_id && in_array($action, ['add', 'remove'])) {
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT IGNORE INTO Favorites (user_id, recipe_id) VALUES (?, ?)");
    } else {
        $stmt = $conn->prepare("DELETE FROM Favorites WHERE user_id = ? AND recipe_id = ?");
    }

    $stmt->bind_param("ii", $user_id, $recipe_id);
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
}

$conn->close();
