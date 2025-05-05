<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

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
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query with filters
$where = ['user_id = :user_id'];
$params = ['user_id' => $user_id];

if (!empty($_GET['search'])) {
    $where[] = 'title LIKE :search';
    $params['search'] = '%' . $_GET['search'] . '%';
}

if (!empty($_GET['diet'])) {
    $where[] = 'diet_preference = :diet';
    $params['diet'] = $_GET['diet'];
}

if (!empty($_GET['cuisine'])) {
    $where[] = 'nationality = :cuisine';
    $params['cuisine'] = $_GET['cuisine'];
}

if (!empty($_GET['difficulty'])) {
    $where[] = 'difficulty_level = :difficulty';
    $params['difficulty'] = $_GET['difficulty'];
}

if (!empty($_GET['meal'])) {
    $where[] = 'meal_type = :meal';
    $params['meal'] = $_GET['meal'];
}

$where_clause = implode(' AND ', $where);

// Get recipes
$sql = "SELECT * FROM Recipes WHERE $where_clause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$recipes = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode(['recipes' => $recipes]); 