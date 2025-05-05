<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$week_start = $_POST['week_start'] ?? null;
$include_breakfast = isset($_POST['include_breakfast']) ? 1 : 0;
$include_lunch = isset($_POST['include_lunch']) ? 1 : 0;
$include_dinner = isset($_POST['include_dinner']) ? 1 : 0;

if (!$week_start) {
    echo json_encode(['success' => false, 'error' => 'Week start date is required']);
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
    
    // Call the stored procedure with new parameters
    $stmt = $pdo->prepare("CALL generate_simple_meal_plan(?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $week_start,
        $include_breakfast,
        $include_lunch,
        $include_dinner
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 