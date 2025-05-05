<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// DB connection
$host = 'localhost';
$port = 3307;
$db   = 'project';
$user = 'root';
$pass = '';
$pdo  = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
    $user, $pass,
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
);

// Fetch all ingredients for the datalist
$allIng = $pdo->query("SELECT ingredient_id, name FROM Ingredients ORDER BY name")
              ->fetchAll();

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Insert into Recipes
    $stmt = $pdo->prepare("
      INSERT INTO Recipes
        (user_id, title, servings, instructions,
         diet_preference, nationality, difficulty_level,
         total_calories, meal_type)
      VALUES
        (:uid, :title, :servings, :instr,
         :diet, :nat, :diff,
         :cal, :meal)
    ");
    $stmt->execute([
        'uid'      => $_SESSION['user_id'],
        'title'    => trim($_POST['title'] ?? ''),
        'servings' => intval($_POST['servings'] ?? 0),
        'instr'    => trim($_POST['instructions'] ?? ''),
        'diet'     => $_POST['diet_preference'] ?: null,
        'nat'      => $_POST['nationality'] ?: null,
        'diff'     => $_POST['difficulty_level'] ?: null,
        'cal'      => $_POST['total_calories'] ?: null,
        'meal'     => $_POST['meal_type'] ?: null,
    ]);
    $rid = $pdo->lastInsertId();

    // 2) Insert into RecipeIngredients (with upsert of new ingredients)
    $insRI = $pdo->prepare("
      INSERT INTO RecipeIngredients
        (recipe_id, ingredient_id, quantity)
      VALUES
        (:rid, :iid, :qty)
    ");
    foreach ($_POST['ingredients'] as $i => $name) {
        $qty  = trim($_POST['quantity'][$i] ?? '');
        $name = trim($name);
        if ($name === '' || $qty === '') continue;

        // Look up or insert ingredient
        $sel = $pdo->prepare("SELECT ingredient_id FROM Ingredients WHERE name = :name");
        $sel->execute(['name' => $name]);
        $row = $sel->fetch();
        if ($row) {
            $iid = $row['ingredient_id'];
        } else {
            $ins = $pdo->prepare("
              INSERT INTO Ingredients
                (name, calories, serving_size_g, measurement_unit, ingredient_category)
              VALUES
                (:name, 0, 0, '', 'Other')
            ");
            $ins->execute(['name' => $name]);
            $iid = $pdo->lastInsertId();
        }

        // Finally insert into join table
        $insRI->execute([
            'rid' => $rid,
            'iid' => $iid,
            'qty' => floatval($qty)
        ]);
    }

    header('Location: my_recipes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Recipe | Tried & Tasted</title>
  <style>
    /* your styles here... */
  </style>
</head>
<body>
  <h2>Add New Recipe</h2>
  <form method="POST">
    <input name="title" placeholder="Recipe Title" required /><br>
    <input name="servings" type="number" min="1" placeholder="Servings" required /><br>
    <textarea name="instructions" rows="5" placeholder="Instructions" required></textarea><br>

    <select name="diet_preference">
      <option value="">-- Diet Preference --</option>
      <?php foreach(['Veg','Non-Veg','Vegan','Only Fish'] as $d): ?>
        <option value="<?=$d?>"><?=$d?></option>
      <?php endforeach;?>
    </select>

    <input list="ingredient-list" disabled style="display:none">
    <datalist id="ingredient-list">
      <?php foreach($allIng as $ing): ?>
        <option value="<?=htmlspecialchars($ing['name'])?>"></option>
      <?php endforeach;?>
    </datalist>

    <input name="nationality" placeholder="Nationality" /><br>

    <select name="difficulty_level">
      <option value="">-- Difficulty --</option>
      <?php foreach(['Beginner','Intermediate','Pro'] as $l): ?>
        <option value="<?=$l?>"><?=$l?></option>
      <?php endforeach;?>
    </select>

    <input name="total_calories" placeholder="Total Calories" /><br>

    <select name="meal_type">
      <option value="">-- Meal Type --</option>
      <?php foreach(['Breakfast','Lunch','Dinner'] as $m): ?>
        <option value="<?=$m?>"><?=$m?></option>
      <?php endforeach;?>
    </select><br><br>

    <h3>Ingredients</h3>
    <div id="ing-list">
      <div class="row">
        <input list="ingredient-list" name="ingredients[]" placeholder="Type or select ingredient" required />
        <input name="quantity[]" placeholder="Quantity (e.g. 2.5)" required />
        <button type="button" onclick="this.parentElement.remove()">×</button>
      </div>
    </div>
    <button type="button" onclick="
      let r=document.querySelector('.row').cloneNode(true);
      r.querySelector('input[list]').value = '';
      r.querySelector('input[name=&quot;quantity[]&quot;]').value = '';
      document.getElementById('ing-list').append(r);
    ">Add Ingredient</button><br><br>

    <button type="submit">Save Recipe</button>
  </form>
</body>
</html>
