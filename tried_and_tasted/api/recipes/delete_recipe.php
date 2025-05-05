<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
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
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = intval($_POST['recipe_id'] ?? 0);

if (!$recipe_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid recipe ID']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Delete recipe ingredients
    $stmt = $pdo->prepare('DELETE FROM RecipeIngredients WHERE recipe_id = :recipe_id');
    $stmt->execute(['recipe_id' => $recipe_id]);

    // Delete recipe
    $stmt = $pdo->prepare('DELETE FROM Recipes WHERE recipe_id = :recipe_id AND user_id = :user_id');
    $stmt->execute([
        'recipe_id' => $recipe_id,
        'user_id' => $user_id
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Recipe not found or unauthorized');
    }

    // Commit transaction
    $pdo->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 