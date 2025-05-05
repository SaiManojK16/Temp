<?php
session_start();
require_once '../includes/recipe_manager.php';

$recipeManager = new RecipeManager($pdo);
$recipes = $recipeManager->getAllRecipes();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recipes</title>
</head>
<body>
    <h1>Recipes</h1>
    
    <?php if (isAdmin()): ?>
        <div class="admin-actions">
            <a href="add_recipe.php">Add New Recipe</a>
        </div>
    <?php endif; ?>
    
    <div class="recipe-list">
        <?php foreach ($recipes as $recipe): ?>
            <div class="recipe">
                <h2><?= htmlspecialchars($recipe['title']) ?></h2>
                <p><?= htmlspecialchars($recipe['description']) ?></p>
                
                <?php if (isAdmin()): ?>
                    <div class="admin-controls">
                        <a href="edit_recipe.php?id=<?= $recipe['recipe_id'] ?>">Edit</a>
                        <a href="delete_recipe.php?id=<?= $recipe['recipe_id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this recipe?')">
                            Delete
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>