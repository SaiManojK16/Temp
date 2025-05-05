<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
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
$dsn  = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$recipe_id = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;

// Fetch recipe
$stmt = $pdo->prepare("SELECT * FROM Recipes WHERE recipe_id = :rid AND user_id = :uid");
$stmt->execute(['rid' => $recipe_id, 'uid' => $user_id]);
$recipe = $stmt->fetch();
if (!$recipe) {
    header('Location: my_recipes.php');
    exit;
}

// Options
$diets = ['Veg','Non-Veg','Vegan','Only Fish'];
$levels = ['Beginner','Intermediate','Pro'];
$meals = ['Breakfast','Lunch','Dinner'];
nationalities = $pdo->query("SELECT DISTINCT nationality FROM Recipes WHERE nationality IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect inputs
    $title = trim($_POST['title'] ?? '');
    $servings = intval($_POST['servings'] ?? 0);
    $instructions = trim($_POST['instructions'] ?? '');
    $diet = $_POST['diet_preference'] ?? null;
    $nationality = trim($_POST['nationality'] ?? '');
    $difficulty = $_POST['difficulty_level'] ?? null;
    $calories = trim($_POST['total_calories'] ?? '');
    $meal = $_POST['meal_type'] ?? null;

    // Validate
    if ($title === '') $errors[] = 'Title is required';
    if ($servings <= 0) $errors[] = 'Servings must be positive';
    if ($instructions === '') $errors[] = 'Instructions are required';
    if ($diet && !in_array($diet, $diets)) $errors[] = 'Invalid diet';
    if ($difficulty && !in_array($difficulty, $levels)) $errors[] = 'Invalid difficulty';
    if ($meal && !in_array($meal, $meals)) $errors[] = 'Invalid meal type';
    if ($calories !== '' && !is_numeric($calories)) $errors[] = 'Calories must be numeric';
    if ($nationality === '') $nationality = null;

    // Update
    if (empty($errors)) {
        $upd = $pdo->prepare(
            "UPDATE Recipes SET
              title = :title,
              servings = :servings,
              instructions = :instr,
              diet_preference = :diet,
              nationality = :nat,
              difficulty_level = :diff,
              total_calories = :cal,
              meal_type = :meal
             WHERE recipe_id = :rid AND user_id = :uid"
        );
        $upd->execute([
            'title'    => $title,
            'servings' => $servings,
            'instr'    => $instructions,
            'diet'     => $diet,
            'nat'      => $nationality,
            'diff'     => $difficulty,
            'cal'      => $calories ?: null,
            'meal'     => $meal,
            'rid'      => $recipe_id,
            'uid'      => $user_id,
        ]);
        header('Location: my_recipes.php');
        exit;
    }
    // On error, preserve submitted
    $recipe = array_merge($recipe, $_POST);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Recipe | Tried & Tasted</title>
  <style>
    body { font-family: 'Segoe UI',sans-serif; background:#f9f9f9; color:#333; padding:20px; }
    form { max-width:600px; margin:auto; display:flex; flex-direction:column; gap:12px; }
    input, textarea, select { padding:8px; border:1px solid #ccc; border-radius:5px; font-size:1rem; }
    button { padding:10px; background:#0077cc; color:#fff; border:none; border-radius:5px; cursor:pointer; }
    .errors { color:#e17055; }
  </style>
</head>
<body>
  <h2>Edit Recipe</h2>
  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>
  <form method="POST">
    <input type="text" name="title" placeholder="Title" value="<?=htmlspecialchars($recipe['title'])?>" required />
    <input type="number" name="servings" placeholder="Servings" min="1" value="<?=htmlspecialchars($recipe['servings'])?>" required />
    <textarea name="instructions" rows="6" placeholder="Instructions" required><?=htmlspecialchars($recipe['instructions'])?></textarea>
    <select name="diet_preference"><option value="">-- Diet --</option>
      <?php foreach ($diets as $d): ?><option value="<?=$d?>" <?=($recipe['diet_preference']===$d)?'selected':''?>><?=$d?></option><?php endforeach; ?>
    </select>
    <select name="nationality"><option value="">-- Nationality --</option>
      <?php foreach ($nationalities as $n): ?><option value="<?=htmlspecialchars($n)?>" <?=($recipe['nationality']===$n)?'selected':''?>><?=htmlspecialchars($n)?></option><?php endforeach; ?>
      <option value="Other" <?=($recipe['nationality']==='Other')?'selected':''?>>Other</option>
    </select>
    <select name="difficulty_level"><option value="">-- Difficulty --</option>
      <?php foreach ($levels as $l): ?><option value="<?=$l?>" <?=($recipe['difficulty_level']===$l)?'selected':''?>><?=$l?></option><?php endforeach; ?>
    </select>
    <input type="text" name="total_calories" placeholder="Calories" value="<?=htmlspecialchars($recipe['total_calories'])?>" />
    <select name="meal_type"><option value="">-- Meal Type --</option>
      <?php foreach ($meals as $m): ?><option value="<?=$m?>" <?=($recipe['meal_type']===$m)?'selected':''?>><?=$m?></option><?php endforeach; ?>
    </select>
    <button type="submit">Save Changes</button>
  </form>
</body>
</html>
