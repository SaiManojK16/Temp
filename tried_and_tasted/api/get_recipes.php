<?php
header('Content-Type: application/json');
// Inline DB connection
$host = 'localhost:3307';
$db = 'project';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}
$sql = "SELECT recipe_id, title FROM Recipes ORDER BY title ASC";
$result = $conn->query($sql);
$recipes = [];
while ($row = $result->fetch_assoc()) {
    $recipes[] = $row;
}
echo json_encode($recipes); 