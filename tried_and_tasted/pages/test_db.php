<?php
require_once 'includes/db_connect.php';

try {
    // Test the connection by executing a simple query
    $result = $pdo->query("SELECT 1");
    
    if ($result) {
        echo "✅ Database connection successful!\n";
        echo "Connection details:\n";
        echo "Host: " . getenv('DB_HOST') . "\n";
        echo "Database: " . getenv('DB_NAME') . "\n";
        echo "User: " . getenv('DB_USER') . "\n";
        
        // Test if we can read from the database
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "\nTables in database:\n";
        foreach ($tables as $table) {
            echo "- " . $table . "\n";
        }
    }
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nConnection details attempted:\n";
    echo "Host: " . getenv('DB_HOST') . "\n";
    echo "Database: " . getenv('DB_NAME') . "\n";
    echo "User: " . getenv('DB_USER') . "\n";
}
?>