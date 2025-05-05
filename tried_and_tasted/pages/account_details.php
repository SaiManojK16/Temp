<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /template/tried_and_tasted/pages/login.php');
    exit;
}

$page_title = 'Account Details | Tried & Tasted';
$current_page = '';
require_once '../includes/header.php';

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
    die('Database connection failed: ' . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $updates = [];
    $params = [];
    if ($full_name) {
        $updates[] = 'full_name = ?';
        $params[] = $full_name;
    }
    if ($password) {
        $updates[] = 'password_hash = ?';
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    if ($updates) {
        $params[] = $user_id;
        $sql = 'UPDATE Users SET ' . implode(', ', $updates) . ' WHERE user_id = ?';
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $success = 'Account updated successfully!';
        } else {
            $error = 'Failed to update account.';
        }
    }
}

// Fetch user info
$stmt = $pdo->prepare('SELECT full_name, email FROM Users WHERE user_id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<style>
.account-container {
    max-width: 500px;
    margin: 3rem auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 2.5rem 2rem 2rem 2rem;
}
.account-title {
    font-size: 1.7rem;
    font-weight: 700;
    color: #e67e22;
    margin-bottom: 2rem;
    text-align: center;
}
.account-form label {
    font-weight: 500;
    color: #636e72;
    margin-bottom: 0.5rem;
    display: block;
}
.account-form input[type="text"],
.account-form input[type="email"],
.account-form input[type="password"] {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 1rem;
    background: #f8f9fa;
}
.account-form input[readonly] {
    background: #f1f2f6;
    color: #888;
}
.account-form button {
    background: #e67e22;
    color: #fff;
    padding: 0.8rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 0.5rem;
}
.account-form button:hover {
    background: #d35400;
}
.success-msg {
    color: #27ae60;
    text-align: center;
    margin-bottom: 1rem;
}
.error-msg {
    color: #e74c3c;
    text-align: center;
    margin-bottom: 1rem;
}
</style>

<div class="account-container">
    <div class="account-title">Account Details</div>
    <?php if ($success): ?>
        <div class="success-msg"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" class="account-form">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

        <label for="password">New Password <span style="color:#888;font-weight:400;">(leave blank to keep current)</span></label>
        <input type="password" id="password" name="password" placeholder="••••••••">

        <button type="submit">Update Account</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 