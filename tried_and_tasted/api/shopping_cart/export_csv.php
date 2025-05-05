<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Not authorized';
    exit;
}

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
    http_response_code(500);
    echo 'Database error';
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT I.name, I.ingredient_category, SC.quantity, SC.measurement_unit
    FROM ShoppingListDetails SC
    JOIN Ingredients I ON SC.ingredient_id = I.ingredient_id
    WHERE SC.list_id IN (SELECT list_id FROM ShoppingList WHERE user_id = :uid)
    ORDER BY I.ingredient_category, I.name
");
$stmt->execute(['uid' => $user_id]);
$items = $stmt->fetchAll();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="shopping_cart.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Category', 'Quantity', 'Unit']);
foreach ($items as $row) {
    fputcsv($output, [$row['name'], $row['ingredient_category'], $row['quantity'], $row['measurement_unit']]);
}
fclose($output);
exit; 