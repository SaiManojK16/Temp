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

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_plan'])) {
        // Save the meal plan
        $meal_date = $_POST['meal_date'];
        $meal_time = $_POST['meal_time'];
        $recipe_id = $_POST['recipe_id'] ?? null;
        $calories_consumed = $_POST['calories_consumed'] ?? null;

        // Insert into mealplan table
        $stmt = $conn->prepare("INSERT INTO mealplan (user_id, meal_date, meal_time) VALUES (:user_id, :meal_date, :meal_time)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':meal_date' => $meal_date,
            ':meal_time' => $meal_time
        ]);

        $meal_id = $conn->lastInsertId(); // Get the last inserted meal plan ID

        // If calories are manually entered or meal skipped
        if ($calories_consumed) {
            $stmt = $conn->prepare("INSERT INTO user_meal_entries (user_id, meal_id, calories_consumed) VALUES (:user_id, :meal_id, :calories_consumed)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':meal_id' => $meal_id,
                ':calories_consumed' => $calories_consumed
            ]);
        } elseif ($recipe_id) {
            $stmt = $conn->prepare("INSERT INTO user_meal_entries (user_id, meal_id, recipe_id) VALUES (:user_id, :meal_id, :recipe_id)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':meal_id' => $meal_id,
                ':recipe_id' => $recipe_id
            ]);
        }
    }
}
?>
