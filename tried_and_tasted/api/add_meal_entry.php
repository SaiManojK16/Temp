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

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
$meal_date = $_POST['meal_date'] ?? null;
$meal_time = $_POST['meal_time'] ?? null;
$recipe_id = !empty($_POST['recipe_id']) ? intval($_POST['recipe_id']) : null;
$custom_title = trim($_POST['custom_title'] ?? '');
$calories = isset($_POST['calories']) ? floatval($_POST['calories']) : null;

if (!$meal_date || !$meal_time || (!$recipe_id && !$custom_title) || !$calories) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Get or create plan_id for this user/week
$week_start = date('Y-m-d', strtotime('Monday this week', strtotime($meal_date)));
$plan_id = null;
$stmt = $conn->prepare("SELECT plan_id FROM meal_plan WHERE user_id = ? AND week_start = ?");
$stmt->bind_param('is', $user_id, $week_start);
$stmt->execute();
$stmt->bind_result($plan_id_result);
if ($stmt->fetch()) {
    $plan_id = $plan_id_result;
}
$stmt->close();
if (!$plan_id) {
    $stmt = $conn->prepare("INSERT INTO meal_plan (user_id, week_start) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $week_start);
    $stmt->execute();
    $plan_id = $stmt->insert_id;
    $stmt->close();
}

// Insert meal entry
$stmt = $conn->prepare("INSERT INTO meal_plan_entry (plan_id, meal_date, meal_time, recipe_id, custom_title, calories, status) VALUES (?, ?, ?, ?, ?, ?, 'Planned')");
$stmt->bind_param('issisd', $plan_id, $meal_date, $meal_time, $recipe_id, $custom_title, $calories);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}