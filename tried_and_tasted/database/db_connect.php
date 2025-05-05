<?php
// Get environment variables
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');

// Check if current user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get appropriate credentials based on user role
if (isAdmin()) {
    $db_user = 'admin';
    $db_pass = 'admin_password';
} else {
    $db_user = 'app_user';
    $db_pass = 'user_password';
}

// Database connection
$dsn = "mysql:host=$db_host;dbname=$db_name;port=3306";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);
    
    // Set timezone
    $pdo->exec("SET time_zone = '+00:00'");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>