<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect guests to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Saved Recipes | Tried & Tasted";
$current_page = 'saved_recipes';
require_once '../includes/header.php';

$host = 'localhost:3307';
$dbname = 'project';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
// Fetch all favorited recipes
$stmt = $pdo->prepare(
    "SELECT R.*
     FROM Recipes R
     JOIN Favorites F ON R.recipe_id = F.recipe_id
     WHERE F.user_id = :uid"
);
$stmt->execute(['uid' => $user_id]);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For filters
$diets = array_unique(array_filter(array_map(fn($r) => $r['diet_preference'], $recipes)));
$cuisines = array_unique(array_filter(array_map(fn($r) => $r['nationality'], $recipes)));
$difficulties = array_unique(array_filter(array_map(fn($r) => $r['difficulty_level'], $recipes)));
$meal_types = array_unique(array_filter(array_map(fn($r) => $r['meal_type'], $recipes)));
?>
<style>
body { font-family: 'Inter', sans-serif; background: #f9f9f9; color: #333; margin:0; }
.saved-header { max-width:1200px; margin:2rem auto 1rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; }
.search-bar { flex:1; min-width:220px; }
.search-bar input { width:100%; padding:0.75rem 1.2rem; border-radius:12px; border:1px solid #e0e0e0; font-size:1rem; }
.filters { display:flex; gap:1rem; flex-wrap:wrap; }
.filters select { padding:0.6rem 1rem; border-radius:10px; border:1px solid #e0e0e0; background:#fff; font-size:1rem; }
.recipes { max-width:1200px; margin:0 auto 2rem; display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1.5rem; }
.recipe-card { background:#fff; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,0.07); padding:1.5rem 1.2rem 1.2rem; position:relative; display:flex; flex-direction:column; transition:box-shadow 0.2s; cursor:pointer; }
.recipe-card:hover { box-shadow:0 8px 24px rgba(230,126,34,0.13); }
.recipe-card h4 { margin:0 0 0.5rem; font-size:1.15rem; font-weight:600; color:#e67e22; }
.recipe-meta { font-size:0.97em; color:#888; margin-bottom:0.7rem; }
.remove-icon { position:absolute; top:16px; right:16px; width:28px; height:28px; background:url('../assets/bookmark_filled_icon.png') no-repeat center; background-size:contain; cursor:pointer; z-index:2; }
.recipe-card .servings { font-size:0.95em; color:#7f8c8d; margin-bottom:0.5rem; }
.recipe-card .meal-type { font-size:0.93em; color:#636e72; margin-bottom:0.5rem; }
@media (max-width:600px) { .saved-header { flex-direction:column; align-items:stretch; } .recipes { grid-template-columns:1fr; } }
</style>
<script>
let allRecipes = <?php echo json_encode($recipes); ?>;
function renderRecipes(recipes) {
  const grid = document.querySelector('.recipes');
  if (!grid) return;
  grid.innerHTML = '';
  if (recipes.length === 0) {
    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;">No recipes found.</p>';
    return;
  }
  recipes.forEach(row => {
    const card = document.createElement('div');
    card.className = 'recipe-card';
    card.innerHTML = `
      <div class="remove-icon" onclick="toggleSave(this, ${row.recipe_id});event.stopPropagation();"></div>
      <h4>${row.title ? escapeHtml(row.title) : ''}</h4>
      <div class="recipe-meta">
        ${row.diet_preference || ''} | ${row.nationality || ''} | ${row.difficulty_level || ''} | Calories: ${row.total_calories || ''}
      </div>
      <div class="servings">Servings: ${row.servings || '-'}</div>
      <div class="meal-type">Meal: ${row.meal_type || '-'}</div>
    `;
    grid.appendChild(card);
  });
}
function escapeHtml(text) {
  return text.replace(/[&<>'"]/g, function(c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c];
  });
}
function filterRecipes() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const diet = document.getElementById('filterDiet').value;
  const cuisine = document.getElementById('filterCuisine').value;
  const diff = document.getElementById('filterDifficulty').value;
  const meal = document.getElementById('filterMeal').value;
  let filtered = allRecipes.filter(r =>
    (!search || (r.title && r.title.toLowerCase().includes(search))) &&
    (!diet || r.diet_preference === diet) &&
    (!cuisine || r.nationality === cuisine) &&
    (!diff || r.difficulty_level === diff) &&
    (!meal || r.meal_type === meal)
  );
  renderRecipes(filtered);
}
function toggleSave(icon, recipeId) {
  fetch('../pages/favorites.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ recipe_id: recipeId, action: 'remove' }).toString()
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Remove card from DOM
      const card = icon.closest('.recipe-card');
      card.parentNode.removeChild(card);
    } else {
      alert(data.error || 'Failed to remove favorite');
    }
  })
  .catch(err => {
    console.error(err);
    alert('Request failed');
  });
}
document.addEventListener('DOMContentLoaded', function() {
  renderRecipes(allRecipes);
  document.getElementById('searchInput').addEventListener('input', filterRecipes);
  document.getElementById('filterDiet').addEventListener('change', filterRecipes);
  document.getElementById('filterCuisine').addEventListener('change', filterRecipes);
  document.getElementById('filterDifficulty').addEventListener('change', filterRecipes);
  document.getElementById('filterMeal').addEventListener('change', filterRecipes);
});
</script>
<div class="saved-header">
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search saved recipes...">
  </div>
  <div class="filters">
    <select id="filterDiet"><option value="">All Diets</option><?php foreach($diets as $d) echo '<option value="'.htmlspecialchars($d).'">'.htmlspecialchars($d).'</option>'; ?></select>
    <select id="filterCuisine"><option value="">All Cuisines</option><?php foreach($cuisines as $c) echo '<option value="'.htmlspecialchars($c).'">'.htmlspecialchars($c).'</option>'; ?></select>
    <select id="filterDifficulty"><option value="">All Levels</option><?php foreach($difficulties as $d) echo '<option value="'.htmlspecialchars($d).'">'.htmlspecialchars($d).'</option>'; ?></select>
    <select id="filterMeal"><option value="">All Meals</option><?php foreach($meal_types as $m) echo '<option value="'.htmlspecialchars($m).'">'.htmlspecialchars($m).'</option>'; ?></select>
  </div>
</div>
<div class="recipes"></div>
<?php require_once '../includes/footer.php'; ?>
