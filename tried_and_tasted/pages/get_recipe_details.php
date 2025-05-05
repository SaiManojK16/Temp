<?php
// Assuming you have already included the database connection
session_start();
$host = 'localhost:3307';
$dbname = 'project';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the recipe_id from the POST request
    $data = json_decode(file_get_contents('php://input'), true);
    $recipe_id = $data['recipe_id'] ?? 0;

    if (!$recipe_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid recipe ID']);
        exit;
    }

    try {
        // Fetch the recipe details
        $sql = "SELECT * FROM Recipes WHERE recipe_id = :recipe_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['recipe_id' => $recipe_id]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipe) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Recipe not found']);
            exit;
        }

        // Fetch ingredients for the recipe
        $ingredients_sql = "SELECT i.name, ri.quantity
                           FROM Ingredients i
                           JOIN RecipeIngredients ri ON i.ingredient_id = ri.ingredient_id
                           WHERE ri.recipe_id = :recipe_id
                           ORDER BY i.name";
        $ingredients_stmt = $conn->prepare($ingredients_sql);
        $ingredients_stmt->execute(['recipe_id' => $recipe_id]);
        $ingredients = $ingredients_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare the response
        $response = [
            'success' => true,
            'recipe' => [
                'title' => $recipe['title'],
                'total_calories' => number_format($recipe['total_calories'], 0),
                'diet_preference' => $recipe['diet_preference'],
                'nationality' => $recipe['nationality'],
                'difficulty_level' => $recipe['difficulty_level'],
                'meal_type' => $recipe['meal_type'],
                'servings' => $recipe['servings'],
                'instructions' => $recipe['instructions'],
                'ingredients' => $ingredients
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);

    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
