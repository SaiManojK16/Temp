<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /template/tried_and_tasted/pages/login.php');
    exit;
}

$page_title = "Shopping Cart | Tried & Tasted";
$current_page = 'shopping_cart';
require_once '../includes/header.php';

// Database connection settings
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
    die("Database connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Fetch shopping cart items
$stmt = $pdo->prepare("
    SELECT SC.ingredient_id, I.name, I.ingredient_category, SC.quantity, SC.measurement_unit,
           SC.added_at
    FROM ShoppingListDetails SC
    JOIN Ingredients I ON SC.ingredient_id = I.ingredient_id
    WHERE SC.list_id IN (SELECT list_id FROM ShoppingList WHERE user_id = :uid)
    ORDER BY SC.added_at DESC
");
$stmt->execute(['uid' => $user_id]);
$items = $stmt->fetchAll();

// Group items by category
$grouped_items = [];
foreach ($items as $item) {
    $cat = $item['ingredient_category'] ?: 'Other';
    if (!isset($grouped_items[$cat])) {
        $grouped_items[$cat] = [];
    }
    $grouped_items[$cat][] = $item;
}
?>

<style>
body { font-family: 'Inter', 'Roboto', Arial, sans-serif; background: #f8f9fa; color: #2d3436; }
.shopping-cart {
    max-width: 1100px;
    margin: 2.5rem auto;
    padding: 0 1rem;
}
.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.2rem;
}
.cart-title {
    font-size: 2.2rem;
    color: #e67e22;
    font-weight: 700;
    margin: 0;
    letter-spacing: -1px;
}
.cart-actions {
    display: flex;
    gap: 1rem;
}
.cart-btn {
    padding: 0.7rem 1.5rem;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(230,126,34,0.07);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.clear-btn {
    background: #ff6b6b;
    color: white;
}
.clear-btn:hover {
    background: #ff5252;
}
.checkout-btn {
    background: #4CAF50;
    color: white;
}
.checkout-btn:hover {
    background: #43A047;
}
.export-btn {
    background: #f1c40f;
    color: #fff;
}
.export-btn:hover {
    background: #f39c12;
}
.category-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    margin-bottom: 2rem;
    overflow: hidden;
}
.category-header {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #e67e22;
    border-bottom: 1px solid #eee;
    letter-spacing: -0.5px;
}
.cart-items {
    padding: 1rem 1.5rem;
}
.cart-item {
    display: flex;
    align-items: center;
    padding: 1.1rem 0;
    border-bottom: 1px solid #f1f2f6;
    background: #fcfcfc;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    transition: box-shadow 0.2s;
}
.cart-item:last-child {
    border-bottom: none;
}
.item-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.item-name {
    font-weight: 600;
    color: #2d3436;
    font-size: 1.1rem;
    margin-bottom: 0.1rem;
}
.item-quantity {
    margin: 0 2rem;
    color: #666;
    font-size: 1rem;
    font-weight: 500;
}
.remove-btn {
    background: none;
    border: none;
    color: #ff6b6b;
    cursor: pointer;
    font-size: 1.5rem;
    padding: 0.5rem;
    border-radius: 50%;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.remove-btn:hover {
    background: #ffeaea;
    color: #ff5252;
}
.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    color: #888;
    font-size: 1.2rem;
}
.empty-cart svg {
    width: 60px;
    height: 60px;
    margin-bottom: 1.2rem;
    color: #e67e22;
}
@media (max-width: 768px) {
    .cart-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .item-quantity {
        margin: 0.5rem 0;
    }
}
</style>

<div class="shopping-cart">
    <div class="cart-header">
        <h1 class="cart-title">Shopping Cart</h1>
        <div class="cart-actions">
            <button class="cart-btn clear-btn" onclick="clearCart()">Clear Cart</button>
            <button class="cart-btn checkout-btn" onclick="checkout()">Checkout</button>
            <a href="../api/shopping_cart/export_csv.php" class="cart-btn export-btn" download><span style="font-size:1.2em;">&#8681;</span> Export CSV</a>
        </div>
    </div>

    <?php if (empty($grouped_items)): ?>
        <div class="empty-cart">
            <svg viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="32" fill="#fff9e6"/><path d="M20 24h24l-2 24H22l-2-24zm4 0V20a8 8 0 1 1 16 0v4" stroke="#e67e22" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="28" cy="48" r="2" fill="#e67e22"/><circle cx="36" cy="48" r="2" fill="#e67e22"/></svg>
            <h2>Your shopping cart is empty</h2>
            <p>Add recipes to your meal plan to see ingredients here</p>
        </div>
    <?php else: ?>
        <?php foreach ($grouped_items as $category => $category_items): ?>
            <div class="category-section">
                <div class="category-header"><?= htmlspecialchars($category) ?></div>
                <div class="cart-items">
                    <?php foreach ($category_items as $item): ?>
                        <div class="cart-item" data-id="<?= $item['ingredient_id'] ?>">
                            <div class="item-info">
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            </div>
                            <div class="item-quantity">
                                <?= htmlspecialchars($item['quantity']) ?> <?= htmlspecialchars($item['measurement_unit']) ?>
                            </div>
                            <button class="remove-btn" onclick="removeItem(<?= $item['ingredient_id'] ?>)" title="Remove"><span style="font-size:1.2em;">&#128465;</span></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function removeItem(ingredientId) {
    if (!confirm('Remove this item from your shopping cart?')) return;
    
    fetch('../api/shopping_cart/remove_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ingredient_id=${ingredientId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`.cart-item[data-id="${ingredientId}"]`).remove();
            // If no items left, show empty cart message
            if (document.querySelectorAll('.cart-item').length === 0) {
                location.reload();
            }
        } else {
            alert(data.error || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove item');
    });
}

function clearCart() {
    if (!confirm('Clear your entire shopping cart?')) return;
    
    fetch('../api/shopping_cart/clear_cart.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to clear cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to clear cart');
    });
}

function checkout() {
    // Implement checkout functionality
    alert('Checkout functionality coming soon!');
}
</script>

<?php require_once '../includes/footer.php'; ?> 