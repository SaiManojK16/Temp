<?php
session_start();
$host = 'localhost:3307';
$dbname = 'project';
$username = 'root';
$password = '';
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Who's logged in?
$user_id = $_SESSION['user_id'] ?? 0;

// Load favorites for this user
$favorites = [];
if ($user_id) {
    $fs = $conn->prepare("SELECT recipe_id FROM favorites WHERE user_id = :uid");
    $fs->execute(['uid' => $user_id]);
    $favorites = $fs->fetchAll(PDO::FETCH_COLUMN);
}

// View logic
$viewAll = (isset($_GET['view']) && $_GET['view'] === 'all');
$limit   = $viewAll ? 12 : 8;

// Filter options
$diets         = ['Veg','Non-Veg','Vegan','Only Fish'];
$difficulties  = ['Beginner','Intermediate','Pro'];
$mealTypes     = ['Breakfast','Lunch','Dinner'];
$nationalities = $conn
    ->query("SELECT DISTINCT nationality FROM Recipes WHERE nationality IS NOT NULL")
    ->fetchAll(PDO::FETCH_COLUMN);

// Search suggestions
if (isset($_GET['suggest'])) {
    $search = $_GET['suggest'];
    $stmt = $conn->prepare("SELECT title FROM Recipes WHERE title LIKE :search LIMIT 5");
    $stmt->execute(['search' => "%$search%"]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    header('Content-Type: application/json');
    echo json_encode($suggestions);
    exit;
}

// ——— updated: FILTER ALWAYS APPLIED ———
$where  = [];
$params = [];

// search
if (!empty($_GET['search'])) {
    $where[]          = 'title LIKE :search';
    $params['search'] = '%'.$_GET['search'].'%';
}

// diet, nationality, difficulty, meal type
foreach (['diet_preference','nationality','difficulty_level','meal_type'] as $f) {
    if (!empty($_GET[$f])) {
        $where[]    = "$f = :$f";
        $params[$f] = $_GET[$f];
    }
}

// calorie level
if (!empty($_GET['calorie_level'])) {
    if ($_GET['calorie_level'] === 'low') {
        $where[] = 'total_calories <= 400';
    } else {
        $where[] = 'total_calories > 400';
    }
}

$where_clause = $where ? 'WHERE '.implode(' AND ', $where) : '';

// Fetch recipes
$sql  = "SELECT * FROM Recipes $where_clause LIMIT $limit";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Explore Recipes | Tried & Tasted";
$current_page = 'explore';
require_once '../includes/header.php';
?>

  <style>
  /* Keep existing styles but remove nav and footer styles */
  .search-bar-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    max-width: 900px;
    margin: 2rem auto 2rem auto;
    width: 100%;
  }
  .search-container {
    flex: 1;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(230,126,34,0.07);
    padding: 2.5rem 2rem 2rem 2rem;
    text-align: center;
    color: #2d3436;
    margin: 0;
    min-width: 0;
  }
  .search-input {
    width: 100%;
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
    border: none;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: #f8f9fa;
    color: #2d3436;
    border: 1.5px solid #e0e0e0;
  }
  .add-recipe-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: #e67e22;
    color: #fff;
    font-size: 2rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(230,126,34,0.13);
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
    border: none;
    margin-left: 0.5rem;
  }
  .add-recipe-circle:hover {
    background: #d35400;
    box-shadow: 0 4px 16px rgba(230,126,34,0.18);
  }
  @media (max-width: 700px) {
    .search-bar-row { flex-direction: column; gap: 1.5rem; }
    .search-container { padding: 1.5rem 1rem 1rem 1rem; }
    .add-recipe-circle { width: 48px; height: 48px; font-size: 1.7rem; }
  }

    /* == RESET & BASE == */
    * { box-sizing: border-box; margin:0; padding:0; }
  body { 
    font-family: 'Inter', sans-serif;
    background: #f8f9fa;
    color: #2d3436;
    line-height: 1.6;
  }

  /* == HERO == */
  .hero {
    background: linear-gradient(135deg, #6c5ce7 0%, #a8a4e6 100%);
    padding: 3rem 1rem;
    text-align: center;
    color: white;
  }
  .hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
  }
  .search-container {
    position: relative;
    max-width: 600px;
    margin: 0 auto;
  }
  .search-input {
    width: 100%;
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
    border: none;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: #f8f9fa;
    color: #2d3436;
    border: 1.5px solid #e0e0e0;
  }
  .search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    margin-top: 0.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: none;
    z-index: 1000;
  }
  .search-suggestions.active {
    display: block;
  }
  .suggestion-item {
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    transition: background 0.2s;
  }
  .suggestion-item:hover {
    background: #f1f2f6;
  }

  /* == FILTERS == */
  .filters {
    background: white;
    padding: 1rem;
    border-radius: 12px;
    margin: 1rem auto;
    max-width: 1200px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }
  .filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
  }
  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  .filter-group label {
    font-size: 0.9rem;
    color: #636e72;
    font-weight: 500;
  }
  .filter-group select {
    padding: 0.75rem;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #2d3436;
    background: #f8f9fa;
  }

  /* == COLLECTIONS == */
  .collections-container {
    position: relative;
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 3rem;
  }
  .collections {
    display: flex;
    gap: 1.5rem;
    overflow-x: hidden;
    scroll-behavior: smooth;
    padding: 1rem 0;
    position: relative;
  }
  .collection-box {
    flex: 0 0 calc(20% - 1.2rem);
    min-width: calc(20% - 1.2rem);
    height: 200px;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
  }
  .collection-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  }
  .collection-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .collection-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
  }
  .scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: all 0.3s;
  }
  .scroll-btn:hover {
    background: #f8f9fa;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  }
  .scroll-btn.prev {
    left: 0;
  }
  .scroll-btn.next {
    right: 0;
  }
  .scroll-btn svg {
    width: 24px;
    height: 24px;
    fill: #2d3436;
  }
  .scroll-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

    /* == RECIPES == */
    .recipes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
    }
    .recipe-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.3s;
    cursor: pointer;
    padding: 1.5rem;
    position: relative;
  }
  .recipe-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  }
  .recipe-content {
    padding: 0;
  }
  .recipe-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #2d3436;
    padding-right: 2rem;
  }
  .recipe-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .recipe-tag {
    background: #f1f2f6;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    color: #636e72;
  }
    .save-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 32px;
    height: 32px;
    background: #f1f2f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s;
    z-index: 2;
  }
  .save-icon:hover {
    transform: scale(1.1);
    background: #dfe6e9;
  }
  .save-icon svg {
    width: 20px;
    height: 20px;
    fill: #b2bec3;
  }
  .save-icon.saved svg {
    fill: #e84393;
  }

  /* == BUTTONS == */
  .btn {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }
  .btn-primary {
    background: #6c5ce7;
    color: white;
  }
  .btn-primary:hover {
    background: #5f4dd0;
  }
  .btn-outline {
    background: transparent;
    border: 2px solid #6c5ce7;
    color: #6c5ce7;
  }
  .btn-outline:hover {
    background: #6c5ce7;
    color: white;
  }

  /* == MODAL == */
    .modal {
      display: none;
      position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
  }
    .modal-content {
    background: white;
    border-radius: 24px;
    max-width: 800px;
    width: 90%;
    margin: 5% auto;
    padding: 2rem;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
  }
    .close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f1f2f6;
    display: flex;
    align-items: center;
    justify-content: center;
      cursor: pointer;
    transition: all 0.2s;
    font-size: 1.5rem;
    color: #636e72;
  }
  .close-btn:hover {
    background: #dfe6e9;
    color: #2d3436;
  }
  .modal-header {
    margin-bottom: 1.5rem;
  }
  .modal-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 0.5rem;
  }
  .modal-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  .modal-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #636e72;
  }
  .modal-meta-item strong {
    color: #2d3436;
  }
  .modal-section {
    margin-bottom: 2rem;
  }
  .modal-section h3 {
    font-size: 1.2rem;
    color: #2d3436;
    margin-bottom: 1rem;
  }
  .modal-ingredients {
    list-style: none;
    padding: 0;
  }
  .modal-ingredients li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f2f6;
  }
  .modal-instructions {
    white-space: pre-line;
    line-height: 1.6;
  }
  .modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
  }
  .modal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    border: none;
    font-weight: 500;
      cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }
  .modal-btn svg {
    width: 20px;
    height: 20px;
  }
  .save-btn {
    background: #e84393;
    color: white;
  }
  .save-btn:hover {
    background: #d63031;
  }
  .meal-plan-btn {
    background: #0984e3;
      color: white;
  }
  .meal-plan-btn:hover {
    background: #0652DD;
  }
  .explore-header {
    max-width: 900px;
    margin: 2.5rem auto 1.5rem auto;
    text-align: left;
  }
  .explore-title {
    font-size: 2.3rem;
    font-weight: 700;
    color: #2d3436;
    margin-bottom: 1.2rem;
  }
  .search-toolbar {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(230,126,34,0.07);
    padding: 1.2rem 2rem;
    max-width: 900px;
    margin: 0 auto 2rem auto;
    width: 100%;
  }
  .search-input {
    flex: 1;
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
    border: none;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: #f8f9fa;
    color: #2d3436;
    border: 1.5px solid #e0e0e0;
  }
  .add-recipe-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: #e67e22;
    color: #fff;
    font-size: 2rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(230,126,34,0.13);
      cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
      border: none;
    margin-left: 0.5rem;
  }
  .add-recipe-circle:hover {
    background: #d35400;
    box-shadow: 0 4px 16px rgba(230,126,34,0.18);
  }
  @media (max-width: 700px) {
    .explore-header, .search-toolbar { max-width: 98vw; }
    .search-toolbar { flex-direction: column; gap: 1.2rem; padding: 1rem; }
    .add-recipe-circle { width: 48px; height: 48px; font-size: 1.7rem; }
    }
  </style>

<div class="explore-header">
  <div class="explore-title">Discover Delicious Recipes</div>
</div>
<div class="search-toolbar">
  <form method="GET" action="explore.php" style="flex:1;display:flex;align-items:center;gap:0.5rem;margin:0;">
    <input type="text" 
           name="search" 
           class="search-input" 
           placeholder="Search for recipes..." 
           value="<?=htmlspecialchars($_GET['search']??'')?>"
           autocomplete="off">
    <div class="search-suggestions"></div>
  </form>
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="add_recipe.php" class="add-recipe-circle" title="Add Recipe">+</a>
  <?php else: ?>
    <a href="pages/login.php" class="add-recipe-circle" title="Login to add recipe">+</a>
  <?php endif; ?>
</div>

<div id="addRecipeModal" class="modal" style="display:none;">
  <div class="modal-content add-recipe-modal-content">
    <button class="close-modal-btn" id="closeAddRecipeModal">&times;</button>
    <h2 style="margin-bottom:1.2rem;">Add New Recipe</h2>
    <form id="addRecipeForm" method="POST" autocomplete="off">
      <input name="title" placeholder="Recipe Title" required style="width:100%;margin-bottom:1rem;" />
      <input name="servings" type="number" min="1" placeholder="Servings" required style="width:100%;margin-bottom:1rem;" />
      <textarea name="instructions" rows="4" placeholder="Instructions" required style="width:100%;margin-bottom:1rem;"></textarea>
      <select name="diet_preference" style="width:100%;margin-bottom:1rem;">
        <option value="">-- Diet Preference --</option>
        <option value="Veg">Veg</option>
        <option value="Non-Veg">Non-Veg</option>
        <option value="Vegan">Vegan</option>
        <option value="Only Fish">Only Fish</option>
      </select>
      <input name="nationality" placeholder="Nationality" style="width:100%;margin-bottom:1rem;" />
      <select name="difficulty_level" style="width:100%;margin-bottom:1rem;">
        <option value="">-- Difficulty --</option>
        <option value="Beginner">Beginner</option>
        <option value="Intermediate">Intermediate</option>
        <option value="Pro">Pro</option>
      </select>
      <input name="total_calories" placeholder="Total Calories" style="width:100%;margin-bottom:1rem;" />
      <select name="meal_type" style="width:100%;margin-bottom:1rem;">
        <option value="">-- Meal Type --</option>
        <option value="Breakfast">Breakfast</option>
        <option value="Lunch">Lunch</option>
        <option value="Dinner">Dinner</option>
      </select>
      <h3 style="margin:1.2rem 0 0.5rem 0;">Ingredients</h3>
      <div id="ing-list">
        <div class="row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
          <input name="ingredients[]" placeholder="Type or select ingredient" required style="flex:2;" />
          <input name="quantity[]" placeholder="Quantity (e.g. 2.5)" required style="flex:1;" />
          <button type="button" class="remove-ing-btn" onclick="this.parentElement.remove()">×</button>
        </div>
      </div>
      <button type="button" id="addIngBtn" style="margin:0.5rem 0 1rem 0;">Add Ingredient</button>
      <div id="addRecipeMsg" style="margin-bottom:1rem;color:#e67e22;"></div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Save Recipe</button>
    </form>
  </div>
</div>

<?php if($viewAll): ?>
<div class="filters">
  <form method="GET" action="explore.php" class="filter-grid">
    <input type="hidden" name="view" value="all">
    <div class="filter-group">
      <label>Diet Preference</label>
      <select name="diet_preference">
        <option value="">Any Diet</option>
        <?php foreach($diets as $d): ?>
          <option value="<?=$d?>" <?=($_GET['diet_preference']??'')===$d?'selected':''?>><?=$d?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group">
      <label>Nationality</label>
      <select name="nationality">
        <option value="">Any Cuisine</option>
        <?php foreach($nationalities as $n): ?>
          <option value="<?=htmlspecialchars($n)?>" <?=($_GET['nationality']??'')===$n?'selected':''?>><?=htmlspecialchars($n)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group">
      <label>Difficulty</label>
      <select name="difficulty_level">
        <option value="">Any Level</option>
        <?php foreach($difficulties as $d): ?>
          <option value="<?=$d?>" <?=($_GET['difficulty_level']??'')===$d?'selected':''?>><?=$d?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group">
      <label>Meal Type</label>
      <select name="meal_type">
        <option value="">Any Meal</option>
        <?php foreach($mealTypes as $m): ?>
          <option value="<?=$m?>" <?=($_GET['meal_type']??'')===$m?'selected':''?>><?=$m?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group">
      <label>Calories</label>
      <select name="calorie_level">
        <option value="">Any Calories</option>
        <option value="low" <?=($_GET['calorie_level']??'')==='low'?'selected':''?>>Low (≤400)</option>
        <option value="high" <?=($_GET['calorie_level']??'')==='high'?'selected':''?>>High (>400)</option>
      </select>
    </div>
    <div class="filter-group" style="align-items: flex-end;">
      <button type="submit" class="btn btn-primary">Apply Filters</button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="collections-container">
  <button class="scroll-btn prev" onclick="scrollCollections('prev')">
    <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
  </button>
  <div class="collections">
    <div class="collection-box" onclick="location='explore.php?diet_preference=Veg'">
      <img src="../assets/Veg.png" alt="Vegetarian">
      <div class="collection-overlay">Vegetarian</div>
    </div>
    <div class="collection-box" onclick="location='explore.php?diet_preference=Non-Veg'">
      <img src="../assets/Non-veg.png" alt="Non-Vegetarian">
      <div class="collection-overlay">Non-Vegetarian</div>
    </div>
    <div class="collection-box" onclick="location='explore.php?diet_preference=Vegan'">
      <img src="../assets/vegan.png" alt="Vegan">
      <div class="collection-overlay">Vegan</div>
    </div>
    <div class="collection-box" onclick="location='explore.php?diet_preference=Only Fish'">
      <img src="../assets/Low-C.png" alt="Fish">
      <div class="collection-overlay">Fish</div>
    </div>
    <div class="collection-box" onclick="location='explore.php?calorie_level=low'">
      <img src="../assets/lowcal.jpg" alt="Low Calorie">
      <div class="collection-overlay">Low Calorie</div>
    </div>
    <div class="collection-box" onclick="location='explore.php?calorie_level=high'">
      <img src="../assets/highprotein.jpg" alt="High Protein">
      <div class="collection-overlay">High Protein</div>
    </div>
    <div class="collection-box" onclick="location='explore.php?difficulty_level=Beginner'">
      <img src="../assets/beginner.jpg" alt="Beginner">
      <div class="collection-overlay">Beginner</div>
    </div>
    <div class="collection-box" onclick="location='explore.php?difficulty_level=Pro'">
      <img src="../assets/chef.jpg" alt="Chef's Choice">
      <div class="collection-overlay">Chef's Choice</div>
    </div>
  </div>
  <button class="scroll-btn next" onclick="scrollCollections('next')">
    <svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
  </button>
</div>

<div class="recipes">
  <?php if(empty($recipes)): ?>
    <p>No recipes found.</p>
  <?php else: ?>
    <?php foreach($recipes as $r): 
      $isSaved = in_array($r['recipe_id'], $favorites) ? 'saved' : '';
    ?>
      <div class="recipe-card" data-id="<?=$r['recipe_id']?>" onclick="showRecipeDetails(<?=$r['recipe_id']?>)">
        <div class="recipe-content">
          <h3 class="recipe-title"><?=htmlspecialchars($r['title'])?></h3>
          <div class="recipe-meta">
            <span class="recipe-tag"><?=htmlspecialchars($r['total_calories'])?> cal</span>
            <span class="recipe-tag"><?=htmlspecialchars($r['diet_preference'])?></span>
            <span class="recipe-tag"><?=htmlspecialchars($r['nationality'])?></span>
            <span class="recipe-tag"><?=htmlspecialchars($r['difficulty_level'])?></span>
          </div>
        </div>
        <div class="save-icon <?=$isSaved?>" onclick="toggleSave(this, <?=$r['recipe_id']?>, event)">
          <svg viewBox="0 0 24 24">
            <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2zm0 15l-5-2.18L7 18V5h10v13z"/>
          </svg>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if(!$viewAll): ?>
<div style="text-align: center; margin: 2rem 0;">
  <a href="?view=all" class="btn btn-primary">View More Recipes</a>
</div>
<?php endif; ?>

<!-- Recipe Modal -->
<div id="recipeModal" class="modal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <div class="modal-header">
      <h2 class="modal-title" id="recipeTitle"></h2>
      <div class="modal-meta">
        <div class="modal-meta-item">
          <strong>Calories:</strong> <span id="recipeCalories"></span> kcal
        </div>
        <div class="modal-meta-item">
          <strong>Diet:</strong> <span id="recipeDiet"></span>
        </div>
        <div class="modal-meta-item">
          <strong>Cuisine:</strong> <span id="recipeCuisine"></span>
        </div>
      </div>
    </div>

    <div class="modal-section">
      <h3>Ingredients</h3>
      <ul class="modal-ingredients" id="recipeIngredients"></ul>
    </div>

    <div class="modal-section">
      <h3>Instructions</h3>
      <p class="modal-instructions" id="recipeInstructions"></p>
    </div>

    <div class="modal-actions">
      <button id="saveRecipe" class="modal-btn save-btn">
        <svg viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg>
        Save to Favorites
      </button>
      <button id="addToMealPlan" class="modal-btn meal-plan-btn">
        <svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        Add to Meal Plan
      </button>
    </div>
    <div id="addToMealPlanForm" style="display:none;margin-top:1.5rem;">
      <label style="font-weight:500;">Day:</label>
      <select id="mealDaySelect" style="margin:0 0.5rem 0 0.5rem;">
        <!-- Days will be populated by JS -->
      </select>
      <label style="font-weight:500;">Meal Time:</label>
      <select id="mealTimeSelect" style="margin:0 0.5rem 0 0.5rem;">
        <option value="Breakfast">Breakfast</option>
        <option value="Lunch">Lunch</option>
        <option value="Dinner">Dinner</option>
        <option value="Snack">Snack</option>
      </select>
      <button id="confirmAddMealBtn" class="btn btn-primary" style="margin-left:0.5rem;">Add</button>
      <span id="addMealPlanMsg" style="margin-left:1rem;"></span>
    </div>
  </div>
</div>

  <script>
  // Dynamic Search
  const searchInput = document.querySelector('.search-input');
  const suggestions = document.querySelector('.search-suggestions');
  let timeoutId;

  searchInput.addEventListener('input', function() {
    clearTimeout(timeoutId);
    const query = this.value.trim();
    
    if (query.length < 2) {
      suggestions.classList.remove('active');
      return;
    }

    timeoutId = setTimeout(() => {
      fetch(`explore.php?suggest=${encodeURIComponent(query)}`)
      .then(response => response.json())
      .then(data => {
          if (data.length > 0) {
            suggestions.innerHTML = data
              .map(item => `<div class="suggestion-item">${item}</div>`)
              .join('');
            suggestions.classList.add('active');
        } else {
            suggestions.classList.remove('active');
          }
        });
    }, 300);
  });

  // Handle suggestion clicks
  suggestions.addEventListener('click', function(e) {
    if (e.target.classList.contains('suggestion-item')) {
      searchInput.value = e.target.textContent;
      suggestions.classList.remove('active');
      searchInput.form.submit();
    }
  });

  // Close suggestions when clicking outside
  document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
      suggestions.classList.remove('active');
    }
  });

  // Toggle Save Recipe
  function toggleSave(element, recipeId, event) {
    event.stopPropagation();
    
    const isSaved = element.classList.contains('saved');
    const action = isSaved ? 'remove' : 'add';
    
      fetch('favorites.php', {
        method: 'POST',
      body: new URLSearchParams({ 
        recipe_id: recipeId, 
        action: action 
      }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
        element.classList.toggle('saved');
        } else {
        alert('Failed to update favorites');
        }
      })
      .catch(error => alert('Request failed'));
    }

  // Show Recipe Details
  function showRecipeDetails(recipeId) {
    fetch('get_recipe_details.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ recipe_id: recipeId })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
      .then(data => {
        if (data.success) {
        const recipe = data.recipe;
        
        // Update modal content
        document.getElementById('recipeTitle').innerText = recipe.title;
        document.getElementById('recipeCalories').innerText = recipe.total_calories;
        document.getElementById('recipeDiet').innerText = recipe.diet_preference;
        document.getElementById('recipeCuisine').innerText = recipe.nationality;
        
        // Update ingredients list
        const ingredientsList = document.getElementById('recipeIngredients');
        ingredientsList.innerHTML = recipe.ingredients
          .map(ingredient => `<li>${ingredient.quantity} ${ingredient.name}</li>`)
          .join('');
        
        // Update instructions
        document.getElementById('recipeInstructions').innerText = recipe.instructions;

        // Update save button state
        const saveBtn = document.getElementById('saveRecipe');
        if (saveBtn) {
          saveBtn.onclick = function(event) {
            event.stopPropagation();
            toggleSaveModal(recipeId, saveBtn);
          };
        }

        // Show modal
        document.getElementById('recipeModal').style.display = "block";
        document.getElementById('recipeModal').setAttribute('data-recipe-id', recipeId);
        document.getElementById('recipeModal').setAttribute('data-recipe-calories', recipe.total_calories);
        } else {
        alert('Failed to fetch recipe details: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to fetch recipe details. Please try again.');
    });
  }

  // Modal close functionality
  window.onload = function() {
    const modal = document.getElementById('recipeModal');
    const closeBtn = document.querySelector('.close-btn');
    
    closeBtn.onclick = function() {
      modal.style.display = "none";
    }

    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }

    // Save recipe from modal
    const confirmAddMealBtn = document.getElementById('confirmAddMealBtn');
    if (confirmAddMealBtn) {
      confirmAddMealBtn.onclick = function(e) {
        e.preventDefault();
        const recipeId = document.getElementById('recipeModal').getAttribute('data-recipe-id');
        const recipeCalories = document.getElementById('recipeModal').getAttribute('data-recipe-calories');
        const mealDate = document.getElementById('mealDaySelect').value;
        const mealTime = document.getElementById('mealTimeSelect').value;
        const msg = document.getElementById('addMealPlanMsg');
        msg.textContent = '';
        fetch('../api/add_meal_entry.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `recipe_id=${encodeURIComponent(recipeId)}&meal_date=${encodeURIComponent(mealDate)}&meal_time=${encodeURIComponent(mealTime)}&calories=${encodeURIComponent(recipeCalories)}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            msg.style.color = '#27ae60';
            msg.textContent = 'Added!';
            setTimeout(() => { document.getElementById('recipeModal').style.display = 'none'; }, 1200);
          } else {
            msg.style.color = '#e74c3c';
            msg.textContent = data.error || 'Failed to add.';
          }
        })
        .catch(() => {
          msg.style.color = '#e74c3c';
          msg.textContent = 'Failed to add.';
        });
      };
    }
  }

  // Add this to your existing JavaScript
  function scrollCollections(direction) {
    const container = document.querySelector('.collections');
    const scrollAmount = container.clientWidth * 0.6; // Scroll by 60% of container width
    
    if (direction === 'next') {
      container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else {
      container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
    
    // Update button states
    updateScrollButtons();
  }

  function updateScrollButtons() {
    const container = document.querySelector('.collections');
    const prevBtn = document.querySelector('.scroll-btn.prev');
    const nextBtn = document.querySelector('.scroll-btn.next');
    
    // Enable/disable prev button
    prevBtn.disabled = container.scrollLeft <= 0;
    
    // Enable/disable next button
    nextBtn.disabled = container.scrollLeft + container.clientWidth >= container.scrollWidth;
  }

  // Update button states on scroll
  document.querySelector('.collections').addEventListener('scroll', updateScrollButtons);

  // Initial button state
  window.addEventListener('load', updateScrollButtons);

  // Modal logic
  const addRecipeModal = document.getElementById('addRecipeModal');
  const openAddRecipeBtn = document.querySelector('.add-recipe-circle');
  const closeAddRecipeModal = document.getElementById('closeAddRecipeModal');
  const addRecipeForm = document.getElementById('addRecipeForm');
  const addRecipeMsg = document.getElementById('addRecipeMsg');
  const addIngBtn = document.getElementById('addIngBtn');

  if (openAddRecipeBtn) {
    openAddRecipeBtn.addEventListener('click', function(e) {
      <?php if (!isset($_SESSION['user_id'])): ?>
        window.location.href = 'pages/login.php';
        return;
      <?php else: ?>
        e.preventDefault();
        addRecipeModal.style.display = 'flex';
        addRecipeMsg.textContent = '';
      <?php endif; ?>
    });
  }
  closeAddRecipeModal.onclick = function() {
    addRecipeModal.style.display = 'none';
    addRecipeForm.reset();
    document.getElementById('ing-list').innerHTML = `<div class="row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;"><input name="ingredients[]" placeholder="Type or select ingredient" required style="flex:2;" /><input name="quantity[]" placeholder="Quantity (e.g. 2.5)" required style="flex:1;" /><button type="button" class="remove-ing-btn" onclick="this.parentElement.remove()">×</button></div>`;
  };
  window.onclick = function(event) {
    if (event.target === addRecipeModal) {
      addRecipeModal.style.display = 'none';
      addRecipeForm.reset();
      document.getElementById('ing-list').innerHTML = `<div class="row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;"><input name="ingredients[]" placeholder="Type or select ingredient" required style="flex:2;" /><input name="quantity[]" placeholder="Quantity (e.g. 2.5)" required style="flex:1;" /><button type="button" class="remove-ing-btn" onclick="this.parentElement.remove()">×</button></div>`;
    }
  };
  addIngBtn.onclick = function() {
    let r = document.createElement('div');
    r.className = 'row';
    r.style = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;';
    r.innerHTML = `<input name="ingredients[]" placeholder="Type or select ingredient" required style="flex:2;" /> <input name="quantity[]" placeholder="Quantity (e.g. 2.5)" required style="flex:1;" /> <button type="button" class="remove-ing-btn" onclick="this.parentElement.remove()">×</button>`;
    document.getElementById('ing-list').appendChild(r);
  };
  addRecipeForm.onsubmit = function(e) {
    e.preventDefault();
    addRecipeMsg.textContent = '';
    const formData = new FormData(addRecipeForm);
    fetch('add_recipe.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.ok ? res.text() : Promise.reject('Failed to save'))
    .then(text => {
      // If redirected, reload to my_recipes.php
      if (text.includes('my_recipes.php')) {
        window.location.href = 'my_recipes.php';
      } else {
        addRecipeMsg.textContent = 'Recipe added!';
        setTimeout(() => { addRecipeModal.style.display = 'none'; addRecipeForm.reset(); }, 1200);
      }
    })
    .catch(() => {
      addRecipeMsg.textContent = 'Failed to add recipe. Please try again.';
    });
  };

  document.getElementById('addToMealPlan').onclick = function() {
    // Show the add to meal plan form
    const form = document.getElementById('addToMealPlanForm');
    form.style.display = 'block';
    // Populate days for the current week
    const daySelect = document.getElementById('mealDaySelect');
    daySelect.innerHTML = '';
    const today = new Date();
    const weekStart = new Date(today);
    weekStart.setDate(today.getDate() - today.getDay() + 1); // Monday
    for (let i = 0; i < 7; i++) {
      const d = new Date(weekStart);
      d.setDate(weekStart.getDate() + i);
      const val = d.toISOString().slice(0,10);
      const label = d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
      const opt = document.createElement('option');
      opt.value = val;
      opt.textContent = label;
      daySelect.appendChild(opt);
    }
  };

  // Add this function to handle modal save toggling:
  function toggleSaveModal(recipeId, btn) {
    const isSaved = btn.innerText.includes('Remove');
    const action = isSaved ? 'remove' : 'add';
    fetch('favorites.php', {
      method: 'POST',
      body: new URLSearchParams({ 
        recipe_id: recipeId, 
        action: action 
      }),
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        btn.innerHTML = isSaved
          ? '<svg view="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg> Save to Favorites'
          : '<svg view="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2zm0 15l-5-2.18L7 18V5h10v13z"/></svg> Remove from Favorites';
      } else {
        alert('Failed to update favorites');
      }
    })
    .catch(error => alert('Request failed'));
  }
</script>

<?php require_once '../includes/footer.php'; ?>
