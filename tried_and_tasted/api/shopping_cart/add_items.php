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

$recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
if (!$recipe_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipe ID']);
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
    // Get or create shopping list for user
    $stmt = $pdo->prepare("SELECT list_id FROM ShoppingList WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $list = $stmt->fetch();
    
    if (!$list) {
        $stmt = $pdo->prepare("INSERT INTO ShoppingList (user_id) VALUES (?)");
        $stmt->execute([$_SESSION['user_id']]);
        $list_id = $pdo->lastInsertId();
    } else {
        $list_id = $list['list_id'];
    }
    
    // Get recipe ingredients
    $stmt = $pdo->prepare("
        SELECT RI.ingredient_id, RI.quantity, I.measurement_unit
        FROM RecipeIngredients RI
        JOIN Ingredients I ON RI.ingredient_id = I.ingredient_id
        WHERE RI.recipe_id = ?
    ");
    $stmt->execute([$recipe_id]);
    $ingredients = $stmt->fetchAll();
    
    // Get user's pantry items
    $stmt = $pdo->prepare("
        SELECT ingredient_id, quantity
        FROM Pantry
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $pantry = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Check each ingredient
    $added_items = [];
    foreach ($ingredients as $ing) {
        $pantry_qty = isset($pantry[$ing['ingredient_id']]) ? floatval($pantry[$ing['ingredient_id']]) : 0;
        $needed_qty = floatval($ing['quantity']);
        
        if ($pantry_qty < $needed_qty) {
            // Add missing quantity to shopping cart
            $missing_qty = $needed_qty - $pantry_qty;
            
            // Check if item already in cart
            $stmt = $pdo->prepare("
                SELECT quantity 
                FROM ShoppingListDetails 
                WHERE list_id = ? AND ingredient_id = ?
            ");
            $stmt->execute([$list_id, $ing['ingredient_id']]);
            $cart_item = $stmt->fetch();
            
            if ($cart_item) {
                // Update existing cart item
                $stmt = $pdo->prepare("
                    UPDATE ShoppingListDetails 
                    SET quantity = quantity + ? 
                    WHERE list_id = ? AND ingredient_id = ?
                ");
                $stmt->execute([$missing_qty, $list_id, $ing['ingredient_id']]);
            } else {
                // Add new cart item
                $stmt = $pdo->prepare("
                    INSERT INTO ShoppingListDetails 
                    (list_id, ingredient_id, quantity, measurement_unit) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $list_id, 
                    $ing['ingredient_id'], 
                    $missing_qty, 
                    $ing['measurement_unit']
                ]);
            }
            
            $added_items[] = [
                'ingredient_id' => $ing['ingredient_id'],
                'quantity' => $missing_qty,
                'unit' => $ing['measurement_unit']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'added_items' => $added_items
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
} 