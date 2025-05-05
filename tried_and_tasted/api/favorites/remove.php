<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['recipe_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing values']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = intval($_POST['recipe_id']);

$stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>