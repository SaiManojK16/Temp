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

// Load recipe
$rid = intval($_GET['recipe_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM Recipes WHERE recipe_id=:rid AND user_id=:uid");
$stmt->execute(['rid'=>$rid,'uid'=>$_SESSION['user_id']]);
$recipe = $stmt->fetch();
if (!$recipe) {
    header('Location: my_recipes.php');
    exit;
}

// All ingredients list
$allIng = $pdo->query("SELECT ingredient_id,name FROM Ingredients ORDER BY name")
              ->fetchAll();

// Current recipe ingredients
$cur    = $pdo->prepare("
  SELECT RI.ingredient_id, I.name, RI.quantity
  FROM RecipeIngredients RI
  JOIN Ingredients I ON RI.ingredient_id = I.ingredient_id
  WHERE RI.recipe_id = :rid
");
$cur->execute(['rid'=>$rid]);
$curList = $cur->fetchAll();

// On submit: update recipe + ingredients
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Update Recipes table
    $u = $pdo->prepare("
      UPDATE Recipes SET
        title=:title,
        servings=:servings,
        instructions=:instr,
        diet_preference=:diet,
        nationality=:nat,
        difficulty_level=:diff,
        total_calories=:cal,
        meal_type=:meal
      WHERE recipe_id=:rid AND user_id=:uid
    ");
    $u->execute([
        'title'=>trim($_POST['title']),
        'servings'=>intval($_POST['servings']),
        'instr'=>trim($_POST['instructions']),
        'diet'=>$_POST['diet_preference']?:null,
        'nat'=>$_POST['nationality']?:null,
        'diff'=>$_POST['difficulty_level']?:null,
        'cal'=>$_POST['total_calories']?:null,
        'meal'=>$_POST['meal_type']?:null,
        'rid'=>$rid,
        'uid'=>$_SESSION['user_id']
    ]);

    // 2) Refresh RecipeIngredients
    $pdo->prepare("DELETE FROM RecipeIngredients WHERE recipe_id=:rid")
        ->execute(['rid'=>$rid]);
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

        // Lookup or insert ingredient
        $sel = $pdo->prepare("SELECT ingredient_id FROM Ingredients WHERE name = :name");
        $sel->execute(['name'=>$name]);
        $row = $sel->fetch();
        if ($row) {
            $iid = $row['ingredient_id'];
        } else {
            // Insert new ingredient
            $ins = $pdo->prepare("
              INSERT INTO Ingredients
                (name, calories, serving_size_g, measurement_unit, ingredient_category)
              VALUES
                (:name, 0, 0, '', 'Other')
            ");
            $ins->execute(['name'=>$name]);
            $iid = $pdo->lastInsertId();
        }

        // Insert join record
        $insRI->execute([
            'rid'=>$rid,
            'iid'=>$iid,
            'qty'=>floatval($qty)
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
  <title>Edit Recipe | Tried & Tasted</title>
  <style>
    /* your styles here… */
  </style>
</head>
<body>
  <h2>Edit Recipe</h2>
  <form method="POST">
    <input name="title" value="<?=htmlspecialchars($recipe['title'])?>" required /><br>
    <input name="servings" type="number" min="1" value="<?=htmlspecialchars($recipe['servings'])?>" required /><br>
    <textarea name="instructions" rows="5" required><?=htmlspecialchars($recipe['instructions'])?></textarea><br>

    <select name="diet_preference">
      <option value="">-- Diet Preference --</option>
      <?php foreach(['Veg','Non-Veg','Vegan','Only Fish'] as $d): ?>
        <option value="<?=$d?>" <?=($recipe['diet_preference']===$d)?'selected':''?>><?=$d?></option>
      <?php endforeach;?>
    </select>

    <input list="ingredient-list" disabled style="display:none">
    <datalist id="ingredient-list">
      <?php foreach($allIng as $ing): ?>
        <option value="<?=htmlspecialchars($ing['name'])?>"></option>
      <?php endforeach;?>
    </datalist>

    <input name="nationality" placeholder="Nationality" value="<?=htmlspecialchars($recipe['nationality'])?>" /><br>

    <select name="difficulty_level">
      <option value="">-- Difficulty --</option>
      <?php foreach(['Beginner','Intermediate','Pro'] as $l): ?>
        <option value="<?=$l?>" <?=($recipe['difficulty_level']===$l)?'selected':''?>><?=$l?></option>
      <?php endforeach;?>
    </select>

    <input name="total_calories" placeholder="Total Calories" value="<?=htmlspecialchars($recipe['total_calories'])?>" /><br>

    <select name="meal_type">
      <option value="">-- Meal Type --</option>
      <?php foreach(['Breakfast','Lunch','Dinner'] as $m): ?>
        <option value="<?=$m?>" <?=($recipe['meal_type']===$m)?'selected':''?>><?=$m?></option>
      <?php endforeach;?>
    </select><br><br>

    <h3>Ingredients</h3>
    <div id="ing-list">
      <?php foreach($curList as $row): ?>
      <div class="row">
        <input list="ingredient-list"
               name="ingredients[]"
               value="<?=htmlspecialchars($row['name'])?>"
               required />
        <input name="quantity[]"
               value="<?=htmlspecialchars($row['quantity'])?>"
               placeholder="Quantity" required />
        <button type="button" onclick="this.parentElement.remove()">×</button>
      </div>
      <?php endforeach;?>
      <?php if (empty($curList)): // ensure one empty row ?>
      <div class="row">
        <input list="ingredient-list" name="ingredients[]" placeholder="Ingredient" required />
        <input name="quantity[]" placeholder="Quantity" required />
        <button type="button" onclick="this.parentElement.remove()">×</button>
      </div>
      <?php endif;?>
    </div>
    <button type="button" onclick="
      let r=document.querySelector('.row').cloneNode(true);
      r.querySelector('input[list]').value='';
      r.querySelector('input[name=&quot;quantity[]&quot;]').value='';
      document.getElementById('ing-list').append(r);
    ">Add Ingredient</button><br><br>

    <button type="submit">Save Changes</button>
  </form>
</body>
</html>
