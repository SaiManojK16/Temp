<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /template/tried_and_tasted/pages/login.php');
    exit;
}

$page_title = "Pantry | Tried & Tasted";
$current_page = 'pantry';
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

// Handle form submission: add new pantry item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ingredient_id = intval($_POST['ingredient_id'] ?? 0);
    $quantity      = trim($_POST['quantity'] ?? '');
    $expiry_date   = $_POST['expiry_date'] ?? '';

    if ($ingredient_id && $quantity && $expiry_date) {
        $stmt = $pdo->prepare(
            "INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
             VALUES (:uid, :iid, :qty, :exp)"
        );
        $stmt->execute([
            'uid' => $user_id,
            'iid' => $ingredient_id,
            'qty' => $quantity,
            'exp' => $expiry_date,
        ]);
    }
    header('Location: pantry.php');
    exit;
}

// Fetch pantry items for display
$stmt = $pdo->prepare(
    "SELECT P.pantry_id, I.name AS item_name, I.ingredient_category, P.quantity, P.expiry_date
     FROM Pantry P
     LEFT JOIN Ingredients I ON P.ingredient_id = I.ingredient_id
     WHERE P.user_id = :uid
     ORDER BY P.expiry_date ASC"
);
$stmt->execute(['uid' => $user_id]);
$items = $stmt->fetchAll();

// Fetch ingredients for dropdown
$ings = $pdo->query("SELECT ingredient_id, name, ingredient_category FROM Ingredients ORDER BY name")->fetchAll();
$categories = array_unique(array_filter(array_map(fn($i) => $i['ingredient_category'], $ings)));

// For expiry alerts
$today = new DateTime();
foreach ($items as &$it) {
    $exp = new DateTime($it['expiry_date']);
    $diff = (int)$today->diff($exp)->format('%R%a');
    if ($diff < 0) {
        $it['expiry_status'] = 'expired';
    } elseif ($diff <= 3) {
        $it['expiry_status'] = 'soon';
    } else {
        $it['expiry_status'] = 'fresh';
    }
}
unset($it);
?>

<style>
body { font-family: 'Inter', sans-serif; background: #f9f9f9; color: #333; margin:0; }
.pantry-header { max-width:1200px; margin:2rem auto 1rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; }
.search-bar { flex:1; min-width:220px; }
.search-bar input { width:100%; padding:0.75rem 1.2rem; border-radius:12px; border:1px solid #e0e0e0; font-size:1rem; }
.filters { display:flex; gap:1rem; flex-wrap:wrap; }
.filters select { padding:0.6rem 1rem; border-radius:10px; border:1px solid #e0e0e0; background:#fff; font-size:1rem; }
.pantry-form { max-width:1200px; margin:0 auto 2rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:center; background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.07); padding:1.2rem 1.5rem; }
.pantry-form select, .pantry-form input[type="text"], .pantry-form input[type="date"] { padding:0.7rem 1rem; border-radius:10px; border:1px solid #e0e0e0; font-size:1rem; }
.pantry-form button { padding:0.7rem 1.5rem; background:#e67e22; color:#fff; border:none; border-radius:10px; font-weight:600; cursor:pointer; transition:background 0.2s; }
.pantry-form button:hover { background:#d35400; }
.pantry-table { max-width:1200px; margin:0 auto 2rem; background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.07); overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
th, td { padding:14px 10px; border-bottom:1px solid #f1f2f6; text-align:left; }
th { background:#fdf6ec; color:#e67e22; font-size:1.05rem; }
tr:last-child td { border-bottom:none; }
.expired { background:#ffeaea !important; color:#e74c3c; }
.soon { background:#fffbe6 !important; color:#e67e22; }
.fresh { background:#f9f9f9; }
.low-stock { background:#fff3e0 !important; color:#e67e22; font-weight:600; }
.remove-btn { background:transparent; border:none; cursor:pointer; color:#e17055; font-size:1.2rem; }
@media (max-width:600px) { .pantry-header { flex-direction:column; align-items:stretch; } .pantry-form { flex-direction:column; align-items:stretch; } .pantry-table { padding:1rem; } }
</style>
<script>
let allItems = <?php echo json_encode($items); ?>;
function renderPantry(items) {
  const tbody = document.getElementById('pantryBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  if (items.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Your pantry is empty.</td></tr>';
    return;
  }
  items.forEach(it => {
    let qtyStr = (it.quantity !== null && it.quantity !== undefined) ? String(it.quantity).trim() : '';
    let lowStock = false;
    if (qtyStr === '1' || qtyStr === '1.0' || qtyStr === '1.00' || /out/i.test(qtyStr)) lowStock = true;
    const tr = document.createElement('tr');
    tr.id = 'row-' + it.pantry_id;
    tr.className = (it.expiry_status || '') + (lowStock ? ' low-stock' : '');
    tr.innerHTML = `
      <td>${escapeHtml(it.item_name)}</td>
      <td>${escapeHtml(it.ingredient_category || '-')}</td>
      <td>${escapeHtml(qtyStr)}</td>
      <td>${escapeHtml(it.expiry_date)}
        ${it.expiry_status === 'expired' ? '<span style="color:#e74c3c;font-weight:600;"> (Expired!)</span>' : (it.expiry_status === 'soon' ? '<span style="color:#e67e22;font-weight:600;"> (Expiring Soon)</span>' : '')}
      </td>
      <td><button class="remove-btn" onclick="removeItem(${it.pantry_id})">&times;</button></td>
    `;
    tbody.appendChild(tr);
  });
}
function escapeHtml(text) {
  return text ? text.replace(/[&<>'"]/g, function(c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c];
  }) : '';
}
function filterPantry() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const cat = document.getElementById('filterCategory').value;
  let filtered = allItems.filter(it =>
    (!search || (it.item_name && it.item_name.toLowerCase().includes(search))) &&
    (!cat || it.ingredient_category === cat)
  );
  renderPantry(filtered);
}
function removeItem(pantryId) {
  if (!confirm('Remove this item from your pantry?')) return;
  fetch('../api/pantry/pantry_api.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ action: 'remove', id: pantryId }).toString()
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('row-' + pantryId).remove();
    } else {
      alert(data.error || 'Failed to remove');
    }
  })
  .catch(err => {
    console.error(err);
    alert('Request failed');
  });
}
document.addEventListener('DOMContentLoaded', function() {
  renderPantry(allItems);
  document.getElementById('searchInput').addEventListener('input', filterPantry);
  document.getElementById('filterCategory').addEventListener('change', filterPantry);
});
</script>
<div class="pantry-header">
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search pantry items...">
  </div>
  <div class="filters">
    <select id="filterCategory"><option value="">All Categories</option><?php foreach($categories as $cat) echo '<option value="'.htmlspecialchars($cat).'">'.htmlspecialchars($cat).'</option>'; ?></select>
  </div>
</div>
<form method="POST" class="pantry-form">
  <select name="ingredient_id" required>
    <option value="">-- Select Ingredient --</option>
    <?php foreach ($ings as $ing): ?>
      <option value="<?= $ing['ingredient_id'] ?>"><?= htmlspecialchars($ing['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="quantity" placeholder="Quantity (e.g. 2)" required />
  <input type="date" name="expiry_date" required />
  <button type="submit">Add to Pantry</button>
</form>
<div class="pantry-table">
  <table>
    <thead>
      <tr><th>Item</th><th>Category</th><th>Quantity</th><th>Expiry</th><th></th></tr>
    </thead>
    <tbody id="pantryBody"></tbody>
  </table>
</div>
<?php require_once '../includes/footer.php'; ?>
