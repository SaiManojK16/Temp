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

$entry_id = isset($_POST['entry_id']) ? intval($_POST['entry_id']) : 0;
if (!$entry_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid entry ID']);
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
    $pdo->beginTransaction();
    
    // Get meal entry details
    $stmt = $pdo->prepare("
        SELECT MPE.*, R.recipe_id, R.title as recipe_title
        FROM meal_plan_entry MPE
        LEFT JOIN Recipes R ON MPE.recipe_id = R.recipe_id
        WHERE MPE.entry_id = ? AND MPE.plan_id IN (
            SELECT plan_id FROM meal_plan WHERE user_id = ?
        )
    ");
    $stmt->execute([$entry_id, $_SESSION['user_id']]);
    $entry = $stmt->fetch();
    
    if (!$entry) {
        throw new Exception('Meal entry not found');
    }
    
    // Update meal status
    $stmt = $pdo->prepare("
        UPDATE meal_plan_entry 
        SET status = 'Consumed', consumed_at = CURRENT_TIMESTAMP 
        WHERE entry_id = ?
    ");
    $stmt->execute([$entry_id]);
    
    // If this is a recipe, update pantry quantities
    if ($entry['recipe_id']) {
        // Get recipe ingredients
        $stmt = $pdo->prepare("
            SELECT RI.ingredient_id, RI.quantity, I.measurement_unit
            FROM RecipeIngredients RI
            JOIN Ingredients I ON RI.ingredient_id = I.ingredient_id
            WHERE RI.recipe_id = ?
        ");
        $stmt->execute([$entry['recipe_id']]);
        $ingredients = $stmt->fetchAll();
        
        // Update pantry quantities
        foreach ($ingredients as $ing) {
            // Check if item exists in pantry
            $stmt = $pdo->prepare("
                SELECT quantity 
                FROM Pantry 
                WHERE user_id = ? AND ingredient_id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $ing['ingredient_id']]);
            $pantry_item = $stmt->fetch();
            
            if ($pantry_item) {
                // Update existing pantry item
                $new_qty = max(0, floatval($pantry_item['quantity']) - floatval($ing['quantity']));
                $stmt = $pdo->prepare("
                    UPDATE Pantry 
                    SET quantity = ? 
                    WHERE user_id = ? AND ingredient_id = ?
                ");
                $stmt->execute([$new_qty, $_SESSION['user_id'], $ing['ingredient_id']]);
            }
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 