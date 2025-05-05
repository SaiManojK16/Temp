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
$meal_date = $_POST['meal_date'] ?? null;
$meal_time = $_POST['meal_time'] ?? null;
$recipe_id = !empty($_POST['recipe_id']) ? intval($_POST['recipe_id']) : null;
$custom_title = trim($_POST['custom_title'] ?? '');
$calories = isset($_POST['calories']) ? floatval($_POST['calories']) : null;

if (!$entry_id || !$meal_date || !$meal_time || (!$recipe_id && !$custom_title) || !$calories) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("UPDATE meal_plan_entry SET meal_date=?, meal_time=?, recipe_id=?, custom_title=?, calories=? WHERE entry_id=?");
$stmt->bind_param('ssidsi', $meal_date, $meal_time, $recipe_id, $custom_title, $calories, $entry_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
} 