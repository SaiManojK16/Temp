<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /template/tried_and_tasted/pages/login.php');
    exit;
}

$page_title = 'My Recipes | Tried & Tasted';
$current_page = 'my_recipes';
require_once '../includes/header.php';

// DB connection
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
    die('Database connection failed: ' . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Get filter options
$diets = $pdo->query("SELECT DISTINCT diet_preference FROM Recipes WHERE user_id = $user_id AND diet_preference IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$cuisines = $pdo->query("SELECT DISTINCT nationality FROM Recipes WHERE user_id = $user_id AND nationality IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$difficulties = $pdo->query("SELECT DISTINCT difficulty_level FROM Recipes WHERE user_id = $user_id AND difficulty_level IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$meal_types = $pdo->query("SELECT DISTINCT meal_type FROM Recipes WHERE user_id = $user_id AND meal_type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

// Initial load of recipes (first page)
$page = 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare('SELECT * FROM Recipes WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $per_page, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$recipes = $stmt->fetchAll();
?>

<style>
.my-recipes-container {
    max-width: 1100px;
    margin: 2rem auto;
    padding: 0 1rem;
}
.my-recipes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.my-recipes-title {
    font-size: 2rem;
    font-weight: 700;
    color: #e67e22;
}
.add-recipe-btn {
    background: #e67e22;
    color: #fff;
    padding: 0.7rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    transition: background 0.2s;
}
.add-recipe-btn:hover {
    background: #d35400;
}
.search-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}
.search-bar {
    flex: 1;
    min-width: 250px;
}
.search-input {
    width: 100%;
    padding: 0.8rem 1.2rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.filter-select {
    padding: 0.8rem 1.2rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    background: #fff;
    min-width: 150px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.recipes-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}
.recipe-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}
.recipe-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}
.recipe-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 0.5rem;
}
.recipe-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.95rem;
}
.recipe-tag {
    background: #f1f2f6;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    color: #636e72;
}
.recipe-actions {
    margin-top: auto;
    display: flex;
    gap: 1rem;
}
.edit-btn, .delete-btn {
    padding: 0.5rem 1.2rem;
    border-radius: 8px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.2s;
    text-decoration: none;
    text-align: center;
    flex: 1;
}
.edit-btn {
    background: #0984e3;
    color: #fff;
}
.edit-btn:hover {
    background: #0652DD;
}
.delete-btn {
    background: #ff7675;
    color: #fff;
}
.delete-btn:hover {
    background: #d63031;
}
.empty-msg {
    text-align: center;
    color: #888;
    margin: 3rem 0;
    font-size: 1.2rem;
}
.loading {
    text-align: center;
    padding: 2rem;
    color: #666;
    font-size: 1.1rem;
}
@media (max-width: 768px) {
    .my-recipes-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    .search-filters {
        flex-direction: column;
    }
    .filter-select {
        width: 100%;
    }
}
</style>

<div class="my-recipes-container">
    <div class="my-recipes-header">
        <div class="my-recipes-title">My Recipes</div>
        <a href="add_recipe.php" class="add-recipe-btn">+ Add Recipe</a>
    </div>

    <div class="search-filters">
        <div class="search-bar">
            <input type="text" id="searchInput" class="search-input" placeholder="Search your recipes...">
        </div>
        <select id="filterDiet" class="filter-select">
            <option value="">All Diets</option>
            <?php foreach($diets as $diet): ?>
                <option value="<?= htmlspecialchars($diet) ?>"><?= htmlspecialchars($diet) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filterCuisine" class="filter-select">
            <option value="">All Cuisines</option>
            <?php foreach($cuisines as $cuisine): ?>
                <option value="<?= htmlspecialchars($cuisine) ?>"><?= htmlspecialchars($cuisine) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filterDifficulty" class="filter-select">
            <option value="">All Levels</option>
            <?php foreach($difficulties as $difficulty): ?>
                <option value="<?= htmlspecialchars($difficulty) ?>"><?= htmlspecialchars($difficulty) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filterMeal" class="filter-select">
            <option value="">All Meals</option>
            <?php foreach($meal_types as $meal): ?>
                <option value="<?= htmlspecialchars($meal) ?>"><?= htmlspecialchars($meal) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="recipes-list" id="recipesList">
        <?php if (empty($recipes)): ?>
            <div class="empty-msg">You haven't added any recipes yet.</div>
        <?php else: ?>
            <?php foreach ($recipes as $r): ?>
                <div class="recipe-card">
                    <div class="recipe-title"><?= htmlspecialchars($r['title']) ?></div>
                    <div class="recipe-meta">
                        <span class="recipe-tag">Cuisine: <?= htmlspecialchars($r['nationality']) ?></span>
                        <span class="recipe-tag">Diet: <?= htmlspecialchars($r['diet_preference']) ?></span>
                        <span class="recipe-tag">Calories: <?= htmlspecialchars($r['total_calories']) ?> kcal</span>
                        <span class="recipe-tag">Level: <?= htmlspecialchars($r['difficulty_level']) ?></span>
                    </div>
                    <div class="recipe-actions">
                        <a href="edit_recipe.php?id=<?= $r['recipe_id'] ?>" class="edit-btn">Edit</a>
                        <button onclick="deleteRecipe(<?= $r['recipe_id'] ?>)" class="delete-btn">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div id="loading" class="loading" style="display: none;">Loading more recipes...</div>
</div>

<script>
let currentPage = 1;
let isLoading = false;
let hasMore = true;
let searchTimeout;

// Function to load more recipes
async function loadMoreRecipes() {
    if (isLoading || !hasMore) return;
    
    isLoading = true;
    document.getElementById('loading').style.display = 'block';
    
    const search = document.getElementById('searchInput').value;
    const diet = document.getElementById('filterDiet').value;
    const cuisine = document.getElementById('filterCuisine').value;
    const difficulty = document.getElementById('filterDifficulty').value;
    const meal = document.getElementById('filterMeal').value;
    
    try {
        const response = await fetch(`../api/recipes/load_more.php?page=${currentPage + 1}&search=${search}&diet=${diet}&cuisine=${cuisine}&difficulty=${difficulty}&meal=${meal}`);
        const data = await response.json();
        
        if (data.recipes.length > 0) {
            const recipesList = document.getElementById('recipesList');
            data.recipes.forEach(recipe => {
                const card = createRecipeCard(recipe);
                recipesList.appendChild(card);
            });
            currentPage++;
        } else {
            hasMore = false;
        }
    } catch (error) {
        console.error('Error loading more recipes:', error);
    }
    
    isLoading = false;
    document.getElementById('loading').style.display = 'none';
}

// Function to create recipe card
function createRecipeCard(recipe) {
    const card = document.createElement('div');
    card.className = 'recipe-card';
    card.innerHTML = `
        <div class="recipe-title">${recipe.title}</div>
        <div class="recipe-meta">
            <span class="recipe-tag">Cuisine: ${recipe.nationality}</span>
            <span class="recipe-tag">Diet: ${recipe.diet_preference}</span>
            <span class="recipe-tag">Calories: ${recipe.total_calories} kcal</span>
            <span class="recipe-tag">Level: ${recipe.difficulty_level}</span>
        </div>
        <div class="recipe-actions">
            <a href="edit_recipe.php?id=${recipe.recipe_id}" class="edit-btn">Edit</a>
            <button onclick="deleteRecipe(${recipe.recipe_id})" class="delete-btn">Delete</button>
        </div>
    `;
    return card;
}

// Function to delete recipe
async function deleteRecipe(recipeId) {
    if (!confirm('Are you sure you want to delete this recipe?')) return;
    
    try {
        const response = await fetch('../api/recipes/delete_recipe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `recipe_id=${recipeId}`
        });
        const data = await response.json();
        
        if (data.success) {
            const card = document.querySelector(`.recipe-card:has(button[onclick="deleteRecipe(${recipeId})"])`);
            card.remove();
            
            // If no recipes left, show empty message
            if (document.querySelectorAll('.recipe-card').length === 0) {
                document.getElementById('recipesList').innerHTML = '<div class="empty-msg">You haven\'t added any recipes yet.</div>';
            }
        } else {
            alert(data.error || 'Failed to delete recipe');
        }
    } catch (error) {
        console.error('Error deleting recipe:', error);
        alert('Failed to delete recipe');
    }
}

// Function to filter recipes
function filterRecipes() {
    currentPage = 1;
    hasMore = true;
    const recipesList = document.getElementById('recipesList');
    recipesList.innerHTML = '<div class="loading">Loading recipes...</div>';
    
    loadMoreRecipes();
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterRecipes, 300);
});

['filterDiet', 'filterCuisine', 'filterDifficulty', 'filterMeal'].forEach(id => {
    document.getElementById(id).addEventListener('change', filterRecipes);
});

// Infinite scroll
window.addEventListener('scroll', () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
        loadMoreRecipes();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
