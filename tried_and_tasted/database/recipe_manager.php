<?php
require_once 'db_connect.php';

class RecipeManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Get all recipes (both admin and regular users can view)
    public function getAllRecipes() {
        $stmt = $this->pdo->query("SELECT * FROM Recipes ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    // Add new recipe (admin only)
    public function addRecipe($title, $description, $user_id) {
        if (!isAdmin()) {
            throw new Exception("Only admin can add recipes");
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO Recipes (title, description, user_id)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$title, $description, $user_id]);
    }
    
    // Update recipe (admin only)
    public function updateRecipe($recipe_id, $title, $description) {
        if (!isAdmin()) {
            throw new Exception("Only admin can update recipes");
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE Recipes 
            SET title = ?, description = ?
            WHERE recipe_id = ?
        ");
        return $stmt->execute([$title, $description, $recipe_id]);
    }
    
    // Delete recipe (admin only)
    public function deleteRecipe($recipe_id) {
        if (!isAdmin()) {
            throw new Exception("Only admin can delete recipes");
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM Recipes WHERE recipe_id = ?");
        return $stmt->execute([$recipe_id]);
    }
    
    // Get single recipe (both admin and regular users can view)
    public function getRecipe($recipe_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Recipes WHERE recipe_id = ?");
        $stmt->execute([$recipe_id]);
        return $stmt->fetch();
    }
}
?>