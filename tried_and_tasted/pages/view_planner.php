<?php
session_start();
include('db.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your meal planner.");
}

$user_id = $_SESSION['user_id'];

// Fetch the user's consumed meals from the consumption table
$sql = "SELECT c.consumed_at, r.title AS recipe_title
        FROM consumption c
        JOIN recipes r ON c.recipe_id = r.recipe_id
        WHERE c.user_id = :user_id
        ORDER BY c.consumed_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);

echo "<h1>Your Meal Planner</h1>";

echo "<table border='1'>
        <tr>
            <th>Meal Date</th>
            <th>Recipe</th>
        </tr>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td>{$row['consumed_at']}</td>
            <td>{$row['recipe_title']}</td>
          </tr>";
}

echo "</table>";
?>
