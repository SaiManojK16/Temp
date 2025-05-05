<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /template/tried_and_tasted/pages/login.php');
    exit;
}
$page_title = "Meal Planner | Tried & Tasted";
$current_page = 'meal_planner';
require_once '../includes/header.php';

// Database connection
$host = 'localhost:3307';
$db = 'project';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID (replace with real session logic)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Get current week's Monday
$today = new DateTime();
$week_start = clone $today;
$week_start->modify('Monday this week');
$week_start_str = $week_start->format('Y-m-d');

// Check if a plan exists for this user and week
$plan_id = null;
$stmt = $conn->prepare("SELECT plan_id FROM meal_plan WHERE user_id = ? AND week_start = ?");
$stmt->bind_param('is', $user_id, $week_start_str);
$stmt->execute();
$stmt->bind_result($plan_id_result);
if ($stmt->fetch()) {
    $plan_id = $plan_id_result;
}
        $stmt->close();

// If not, create a new plan for this week
if (!$plan_id) {
    $stmt = $conn->prepare("INSERT INTO meal_plan (user_id, week_start) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $week_start_str);
                $stmt->execute();
    $plan_id = $stmt->insert_id;
                $stmt->close();
}

// Fetch all meal entries for this plan
$meal_entries = [];
$sql = "SELECT * FROM meal_plan_entry WHERE plan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $plan_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $meal_entries[$row['meal_date']][$row['meal_time']] = $row;
}
$stmt->close();

// Prepare week days
$days = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $week_start;
    $d->modify("+{$i} days");
    $days[] = $d->format('Y-m-d');
}
$day_labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$meal_times = ['Breakfast','Lunch','Dinner','Snack'];

// Calculate stats
$totalCalories = 0;
$completed = 0;
$skipped = 0;
$substituted = 0;
foreach ($meal_entries as $date => $meals) {
  foreach ($meals as $meal => $entry) {
    if ($entry['status'] === 'Skipped') {
      $skipped++;
    } elseif ($entry['status'] === 'Substituted') {
      $substituted++;
      $totalCalories += floatval($entry['calories']);
    } else { // Planned or Completed
      $completed++;
      $totalCalories += floatval($entry['calories']);
    }
  }
}

// Today's meals
$todayDate = (new DateTime())->format('Y-m-d');
$todayMeals = [];
foreach ($meal_times as $meal) {
  $todayMeals[$meal] = isset($meal_entries[$todayDate][$meal]) ? $meal_entries[$todayDate][$meal] : null;
}
?>

    <style>
.mealplanner-container {
  display: flex;
  min-height: 100vh;
  background: #f8f9fa;
}
.sidebar {
  width: 220px;
  background: #fff;
  border-right: 1px solid #f1f2f6;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 1rem 1rem 1rem;
  gap: 2rem;
}
.sidebar .logo {
  font-size: 1.3rem;
  font-weight: 700;
  color: #27ae60;
  margin-bottom: 2rem;
}
.sidebar-nav {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
.sidebar-nav a {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #636e72;
  text-decoration: none;
  font-weight: 500;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  transition: background 0.2s, color 0.2s;
}
.sidebar-nav a.active, .sidebar-nav a:hover {
  background: #eafaf1;
  color: #27ae60;
}
.sidebar .logout {
  margin-top: auto;
  color: #e74c3c;
  font-weight: 500;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
            border-radius: 8px;
  transition: background 0.2s;
}
.sidebar .logout:hover {
  background: #fdecea;
}

.main-content {
  flex: 1;
  padding: 2.5rem 3vw 2rem 3vw;
  max-width: 1200px;
  margin: 0 auto;
}
.mealplanner-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.mealplanner-header input[type="text"] {
  font-size: 1.3rem;
  font-weight: 600;
  border: none;
  background: transparent;
  border-bottom: 2px solid #e67e22;
  padding: 0.25rem 0.5rem;
  width: 320px;
  color: #2d3436;
}
.mealplanner-header .toggle {
  display: flex;
  gap: 1rem;
}
.toggle-btn {
  background: #fff;
  border: 1px solid #e67e22;
  color: #e67e22;
  padding: 0.5rem 1.2rem;
  border-radius: 20px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.toggle-btn.active, .toggle-btn:hover {
  background: #e67e22;
  color: #fff;
}
.stats-bar {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 1rem 2rem;
  box-shadow: 0 2px 8px rgba(230,126,34,0.07);
            display: flex;
            flex-direction: column;
  align-items: center;
  min-width: 140px;
}
.stat-card .stat-label {
  color: #7f8c8d;
  font-size: 0.95rem;
}
.stat-card .stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #e67e22;
}

/* Calendar grid */
.meal-calendar {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 12px rgba(230,126,34,0.07);
  padding: 2rem 1rem;
  margin-bottom: 2rem;
}
.calendar-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 1rem;
}
.calendar-table th, .calendar-table td {
  text-align: center;
  padding: 0.5rem 0.25rem;
}
.calendar-table th {
  color: #636e72;
            font-size: 1.1rem;
  font-weight: 600;
}
.meal-slot {
  background: #fdf6ec;
  border-radius: 10px;
  min-width: 120px;
  min-height: 110px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  position: relative;
  cursor: pointer;
  transition: box-shadow 0.2s;
}
.meal-slot:hover {
  box-shadow: 0 4px 16px rgba(230,126,34,0.13);
}
.meal-slot .meal-img {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
  margin-bottom: 0.25rem;
}
.meal-slot .meal-title {
            font-size: 1rem;
  font-weight: 500;
  color: #2d3436;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 90px;
}
.meal-slot .meal-servings {
  font-size: 0.85rem;
  color: #7f8c8d;
}
.meal-slot .meal-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}
.meal-slot .meal-action-btn {
  background: #fff;
  border: 1px solid #e67e22;
  color: #e67e22;
  border-radius: 6px;
  padding: 0.2rem 0.7rem;
  font-size: 0.9rem;
            cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.meal-slot .meal-action-btn:hover {
  background: #e67e22;
  color: #fff;
}
.meal-slot.empty {
  color: #b2bec3;
  background: #f1f2f6;
  border: 1px dashed #e67e22;
  font-size: 1.1rem;
  justify-content: center;
}
.meal-slot.skipped {
  background: #f1f2f6 !important;
  color: #b2bec3;
  border: 1px dashed #b2bec3;
  position: relative;
  opacity: 0.7;
}
.meal-slot.substituted {
  background: #fffbe6 !important;
  border: 1px solid #ffe082;
  color: #e67e22;
  position: relative;
}
.meal-status-label {
  position: absolute;
  top: 8px;
  left: 8px;
  background: #e67e22;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 6px;
  padding: 2px 8px;
  z-index: 2;
}
.meal-slot.skipped .meal-status-label {
  background: #b2bec3;
  color: #fff;
}
.meal-slot .meal-actions button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 900px) {
  .mealplanner-container {
    flex-direction: column;
  }
  .sidebar {
            width: 100%;
    flex-direction: row;
    justify-content: space-between;
    padding: 1rem;
    border-right: none;
    border-bottom: 1px solid #f1f2f6;
  }
  .main-content {
    padding: 1rem;
  }
}
</style>

<div class="mealplanner-container">
  <main class="main-content">
    <div class="mealplanner-header">
      <input type="text" value="My Meal Plan (Week of <?=$week_start->format('M j')?>)" placeholder="Meal plan name" />
      <div class="toggle">
        <button class="toggle-btn active" id="todayBtn">Today</button>
        <button class="toggle-btn" id="weekBtn">This Week</button>
      </div>
    </div>
    <div class="stats-bar">
      <div class="stat-card">
        <div class="stat-label">Total Calories</div>
        <div class="stat-value" id="statCalories">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Completed</div>
        <div class="stat-value" id="statCompleted">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Skipped</div>
        <div class="stat-value" id="statSkipped">0</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Substituted</div>
        <div class="stat-value" id="statSubstituted">0</div>
      </div>
    </div>
    <div class="meal-calendar" id="mealCalendar">
      <table class="calendar-table">
        <thead>
          <tr>
            <th>Meal</th>
            <?php foreach ($day_labels as $label): ?>
              <th><?=$label?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($meal_times as $meal): ?>
            <tr>
              <th><?=htmlspecialchars($meal)?></th>
              <?php foreach ($days as $date): ?>
                <?php if (isset($meal_entries[$date][$meal])): 
                  $entry = $meal_entries[$date][$meal];
                  $status = $entry['status'];
                  $isSkipped = $status === 'Skipped';
                  $isSubstituted = $status === 'Substituted';
                  $slotClass = $isSkipped ? 'skipped' : ($isSubstituted ? 'substituted' : '');
                ?>
                  <td>
                    <div class="meal-slot <?=$slotClass?>" data-entry-id="<?=htmlspecialchars($entry['entry_id'])?>">
                      <?php if ($isSkipped): ?>
                        <span class="meal-status-label">Skipped</span>
                      <?php elseif ($isSubstituted): ?>
                        <span class="meal-status-label">Substituted</span>
                      <?php endif; ?>
                      <div class="meal-title">
                        <?=htmlspecialchars($entry['custom_title'] ?: 'Recipe #'.$entry['recipe_id'])?>
                      </div>
                      <div class="meal-servings">
                        <?=htmlspecialchars($entry['calories'])?> kcal
                      </div>
                      <?php if ($isSubstituted && !empty($entry['notes'])): ?>
                        <div style="font-size:0.9rem;color:#e67e22;margin:0.5rem 0;word-break:break-word;">"<?=htmlspecialchars($entry['notes'])?>"</div>
                      <?php endif; ?>
                      <div class="meal-actions">
                        <button class="meal-action-btn" <?=($isSkipped||$isSubstituted)?'disabled':''?>>Edit</button>
                        <button class="meal-action-btn" <?=($isSkipped||$isSubstituted)?'disabled':''?>>Skip</button>
                        <button class="meal-action-btn" <?=($isSkipped||$isSubstituted)?'disabled':''?>>Substitute</button>
                        <button class="meal-action-btn" onclick="markConsumed(<?=htmlspecialchars($entry['entry_id'])?>)" <?=($isSkipped||$isSubstituted)?'disabled':''?>>Mark as Consumed</button>
                      </div>
                    </div>
                  </td>
                <?php else: ?>
                  <td><div class="meal-slot empty">Add meal</div></td>
                <?php endif; ?>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div id="todayView" style="display:none;">
      <h2 style="margin-bottom:1.5rem;">Today's Meals (<?=date('D, M j')?>)</h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;">
        <?php foreach ($meal_times as $meal): $entry = $todayMeals[$meal]; ?>
          <div class="meal-slot <?php
            if ($entry) {
              if ($entry['status'] === 'Skipped') echo 'skipped';
              elseif ($entry['status'] === 'Substituted') echo 'substituted';
            } else {
              echo 'empty';
            }
          ?>" style="min-height:140px;">
            <div style="font-weight:600;font-size:1.1rem;margin-bottom:0.5rem;"><?=$meal?></div>
            <?php if ($entry): ?>
              <?php if ($entry['status'] === 'Skipped'): ?>
                <span class="meal-status-label">Skipped</span>
              <?php elseif ($entry['status'] === 'Substituted'): ?>
                <span class="meal-status-label">Substituted</span>
              <?php endif; ?>
              <div class="meal-title" style="margin-bottom:0.5rem;">
                <?=htmlspecialchars($entry['custom_title'] ?: 'Recipe #'.$entry['recipe_id'])?>
              </div>
              <div class="meal-servings">
                <?=htmlspecialchars($entry['calories'])?> kcal
              </div>
              <?php if ($entry['status'] === 'Substituted' && !empty($entry['notes'])): ?>
                <div style="font-size:0.9rem;color:#e67e22;margin:0.5rem 0;word-break:break-word;">"<?=htmlspecialchars($entry['notes'])?>"</div>
              <?php endif; ?>
            <?php else: ?>
              <div style="color:#b2bec3;">Add meal</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>

<!-- Add Meal Modal -->
<div id="addMealModal" style="display:none;position:fixed;z-index:2000;inset:0;background:rgba(0,0,0,0.3);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div style="background:#fff;padding:2rem 2.5rem;border-radius:18px;max-width:400px;width:100%;position:relative;">
    <button id="closeAddMealModal" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
    <h2 style="margin-bottom:1.5rem;">Add Meal</h2>
    <form id="addMealForm">
      <input type="hidden" name="meal_date" id="addMealDate">
      <input type="hidden" name="meal_time" id="addMealTime">
      <label style="font-weight:500;">Select Recipe</label>
      <select name="recipe_id" id="recipeSelect" style="width:100%;padding:0.5rem;margin-bottom:1rem;">
        <option value="">-- Choose from your recipes --</option>
      </select>
      <div style="text-align:center;margin:1rem 0;color:#888;">or</div>
      <label style="font-weight:500;">Custom Meal Title</label>
      <input type="text" name="custom_title" id="customTitle" style="width:100%;padding:0.5rem;margin-bottom:1rem;">
      <label style="font-weight:500;">Calories</label>
      <input type="number" name="calories" id="customCalories" style="width:100%;padding:0.5rem;margin-bottom:1.5rem;">
      <button type="submit" class="toggle-btn" style="width:100%;">Add Meal</button>
    </form>
  </div>
</div>

<!-- Substitute Meal Modal -->
<div id="substituteMealModal" style="display:none;position:fixed;z-index:2000;inset:0;background:rgba(0,0,0,0.3);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div style="background:#fff;padding:2rem 2.5rem;border-radius:18px;max-width:400px;width:100%;position:relative;">
    <button id="closeSubstituteMealModal" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
    <h2 style="margin-bottom:1.5rem;">Substitute Meal</h2>
    <form id="substituteMealForm">
      <input type="hidden" name="entry_id" id="subEntryId">
      <label style="font-weight:500;">Calories Consumed</label>
      <input type="number" name="calories" id="subCalories" style="width:100%;padding:0.5rem;margin-bottom:1rem;" required>
      <label style="font-weight:500;">What did you eat?</label>
      <textarea name="description" id="subDescription" style="width:100%;padding:0.5rem;margin-bottom:1.5rem;" rows="3" required></textarea>
      <button type="submit" class="toggle-btn" style="width:100%;">Save Substitute</button>
    </form>
  </div>
</div>

<script>
// Toggle between Today and Week view
const todayBtn = document.getElementById('todayBtn');
const weekBtn = document.getElementById('weekBtn');
const mealCalendar = document.getElementById('mealCalendar');
const todayView = document.getElementById('todayView');

todayBtn.onclick = function() {
  todayBtn.classList.add('active');
  weekBtn.classList.remove('active');
  mealCalendar.style.display = 'none';
  todayView.style.display = 'block';
}
weekBtn.onclick = function() {
  weekBtn.classList.add('active');
  todayBtn.classList.remove('active');
  mealCalendar.style.display = 'block';
  todayView.style.display = 'none';
}
mealCalendar.style.display = 'block';
todayView.style.display = 'none';

// --- Add Meal Modal Logic ---
const addMealModal = document.getElementById('addMealModal');
const closeAddMealModal = document.getElementById('closeAddMealModal');
const addMealForm = document.getElementById('addMealForm');
let currentMealDate = null;
let currentMealTime = null;

const mealCalendarTable = document.querySelector('.calendar-table');
if (mealCalendarTable) {
  mealCalendarTable.addEventListener('click', function(e) {
    const slot = e.target.closest('.meal-slot.empty');
    if (slot) {
      // Find the date and meal time for this slot
      const cell = slot.closest('td');
      const row = slot.closest('tr');
      const mealTime = row.querySelector('th').innerText.trim();
      const dayIndex = Array.from(cell.parentNode.children).indexOf(cell) - 1;
      const weekStart = new Date('<?=$week_start->format('Y-m-d')?>');
      const mealDate = new Date(weekStart.getTime() + dayIndex * 24*60*60*1000);
      const mealDateStr = mealDate.toISOString().slice(0,10);
      document.getElementById('addMealDate').value = mealDateStr;
      document.getElementById('addMealTime').value = mealTime;
      currentMealDate = mealDateStr;
      currentMealTime = mealTime;
      addMealModal.style.display = 'flex';
      // Fetch recipes for the dropdown
      fetch('../api/get_recipes.php')
        .then(res => res.json())
        .then(data => {
          const select = document.getElementById('recipeSelect');
          select.innerHTML = '<option value="">-- Choose from your recipes --</option>';
          data.forEach(r => {
            select.innerHTML += `<option value="${r.recipe_id}">${r.title}</option>`;
          });
        });
    }
  });
}

closeAddMealModal.onclick = function() {
  addMealModal.style.display = 'none';
  addMealForm.reset();
};

addMealForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // First add to meal plan
    fetch('../api/add_meal_entry.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // If a recipe was selected, check pantry and add missing items to cart
            const recipeId = formData.get('recipe_id');
            if (recipeId) {
                return fetch('../api/shopping_cart/add_items.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `recipe_id=${recipeId}`
                });
            }
            return Promise.resolve({ success: true });
        }
        throw new Error(data.error || 'Failed to add meal');
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Show notification if items were added to cart
            if (data.added_items && data.added_items.length > 0) {
                const count = data.added_items.length;
                alert(`${count} ingredient${count > 1 ? 's' : ''} added to shopping cart`);
            }
            location.reload();
        } else {
            throw new Error(data.error || 'Failed to process ingredients');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Failed to add meal');
    });
});

// --- Edit Meal Modal Logic ---
Array.from(document.querySelectorAll('.meal-slot .meal-action-btn')).forEach(btn => {
  if (btn.textContent.trim() === 'Edit') {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const slot = this.closest('.meal-slot');
      const cell = slot.closest('td');
      const row = slot.closest('tr');
      const mealTime = row.querySelector('th').innerText.trim();
      const dayIndex = Array.from(cell.parentNode.children).indexOf(cell) - 1;
      const weekStart = new Date('<?=$week_start->format('Y-m-d')?>');
      const mealDate = new Date(weekStart.getTime() + dayIndex * 24*60*60*1000);
      const mealDateStr = mealDate.toISOString().slice(0,10);
      // Get entry_id from PHP-rendered data attribute
      const entryId = slot.getAttribute('data-entry-id');
      // Pre-fill modal
      document.getElementById('addMealDate').value = mealDateStr;
      document.getElementById('addMealTime').value = mealTime;
      addMealModal.setAttribute('data-edit', '1');
      addMealModal.setAttribute('data-entry-id', entryId);
      // Fetch recipes for the dropdown
      fetch('../api/get_recipes.php')
        .then(res => res.json())
        .then(data => {
          const select = document.getElementById('recipeSelect');
          select.innerHTML = '<option value="">-- Choose from your recipes --</option>';
          data.forEach(r => {
            select.innerHTML += `<option value="${r.recipe_id}">${r.title}</option>`;
          });
          // Pre-select recipe or fill custom
          const mealTitle = slot.querySelector('.meal-title').innerText;
          const mealCalories = slot.querySelector('.meal-servings').innerText.replace('kcal','').trim();
          // Try to match recipe
          let found = false;
          data.forEach(r => {
            if (r.title === mealTitle) {
              select.value = r.recipe_id;
              found = true;
            }
          });
          if (!found) {
            select.value = '';
            document.getElementById('customTitle').value = mealTitle;
          } else {
            document.getElementById('customTitle').value = '';
          }
          document.getElementById('customCalories').value = mealCalories;
        });
      addMealModal.style.display = 'flex';
    });
  }
});

// --- Skip Meal Logic ---
Array.from(document.querySelectorAll('.meal-slot .meal-action-btn')).forEach(btn => {
  if (btn.textContent.trim() === 'Skip') {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const slot = this.closest('.meal-slot');
      const entryId = slot.getAttribute('data-entry-id');
      if (!entryId) return;
      if (!confirm('Are you sure you want to skip this meal?')) return;
      fetch('../api/skip_meal_entry.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'entry_id=' + encodeURIComponent(entryId)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Failed to skip meal: ' + (data.error || 'Unknown error'));
        }
      });
    });
  }
});

// --- Substitute Meal Modal Logic ---
const substituteMealModal = document.getElementById('substituteMealModal');
const closeSubstituteMealModal = document.getElementById('closeSubstituteMealModal');
const substituteMealForm = document.getElementById('substituteMealForm');

Array.from(document.querySelectorAll('.meal-slot .meal-action-btn')).forEach(btn => {
  if (btn.textContent.trim() === 'Substitute') {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const slot = this.closest('.meal-slot');
      const entryId = slot.getAttribute('data-entry-id');
      document.getElementById('subEntryId').value = entryId;
      substituteMealModal.style.display = 'flex';
    });
  }
});

closeSubstituteMealModal.onclick = function() {
  substituteMealModal.style.display = 'none';
  substituteMealForm.reset();
};

substituteMealForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(substituteMealForm);
  fetch('../api/substitute_meal_entry.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert('Failed to substitute meal: ' + (data.error || 'Unknown error'));
    }
  });
};

// Update stats bar
window.addEventListener('DOMContentLoaded', function() {
  document.getElementById('statCalories').innerText = '<?=round($totalCalories)?>';
  document.getElementById('statCompleted').innerText = '<?=$completed?>';
  document.getElementById('statSkipped').innerText = '<?=$skipped?>';
  document.getElementById('statSubstituted').innerText = '<?=$substituted?>';
});

function markConsumed(entryId) {
    if (!confirm('Mark this meal as consumed? This will update your pantry quantities.')) return;
    
    fetch('../api/meal_planner/mark_consumed.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `entry_id=${entryId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to mark meal as consumed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark meal as consumed');
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
