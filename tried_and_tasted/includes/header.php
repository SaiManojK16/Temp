<?php
// session_start(); // Removed to avoid duplicate session warnings
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo $page_title ?? 'Tried & Tasted'; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #fff;
      color: #2d3436;
      line-height: 1.6;
    }

    /* Navigation */
    nav {
      background: #fff;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .logo {
      font-size: 1.6rem;
      font-weight: 600;
      color: #e67e22;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }

    .nav-links a {
      text-decoration: none;
      color: #7f8c8d;
      font-weight: 500;
      transition: color 0.2s;
    }

    .nav-links a:hover {
      color: #e67e22;
    }

    .nav-links a.active {
      color: #e67e22;
      font-weight: 600;
    }

    @media (max-width: 768px) {
      nav {
        padding: 1rem;
      }
      
      .nav-links {
        gap: 1rem;
      }
    }

    .profile-menu-container {
      position: relative;
      display: inline-block;
    }
    .profile-btn {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.5rem;
      margin-left: 1.2rem;
      vertical-align: middle;
    }
    .profile-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 2.5rem;
      background: #fff;
      min-width: 160px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.12);
      border-radius: 8px;
      z-index: 200;
      flex-direction: column;
      padding: 0.5rem 0;
    }
    .profile-dropdown a {
      color: #2d3436;
      padding: 0.75rem 1.5rem;
      text-decoration: none;
      display: block;
      font-size: 1rem;
      transition: background 0.2s;
    }
    .profile-dropdown a:hover {
      background: #f6f6f6;
    }
    .profile-menu-container.open .profile-dropdown {
      display: flex;
    }
    @media (max-width: 768px) {
      .profile-dropdown {
        right: 0;
        left: auto;
      }
    }
  </style>
</head>
<body>
  <nav>
    <a href="home.php" class="logo">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
      </svg>
      Tried & Tasted
    </a>
    <div class="nav-links">
      <a href="explore.php" <?php echo $current_page === 'explore' ? 'class=\"active\"' : ''; ?>>Explore</a>
      <a href="meal_planner.php" <?php echo $current_page === 'meal_planner' ? 'class=\"active\"' : ''; ?>>Meal Planner</a>
      <a href="saved_recipe.php" <?php echo $current_page === 'saved_recipes' ? 'class=\"active\"' : ''; ?>>Saved</a>
      <a href="pantry.php" <?php echo $current_page === 'pantry' ? 'class=\"active\"' : ''; ?>>Pantry</a>
      <a href="shopping_cart.php" <?php echo $current_page === 'shopping_cart' ? 'class=\"active\"' : ''; ?>>🛒</a>
      <div class="profile-menu-container">
        <button class="profile-btn" onclick="toggleProfileMenu(event)">
          <span class="profile-icon">👤</span>
        </button>
        <div class="profile-dropdown" id="profileDropdown">
          <a href="my_recipes.php">My Recipes</a>
          <a href="account_details.php">Account Details</a>
          <a href="/template/tried_and_tasted/logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>
  <main class="main-content">
  <script>
    function toggleProfileMenu(e) {
      e.stopPropagation();
      var container = document.querySelector('.profile-menu-container');
      container.classList.toggle('open');
    }
    document.addEventListener('click', function(e) {
      var container = document.querySelector('.profile-menu-container');
      if (container && container.classList.contains('open')) {
        container.classList.remove('open');
      }
    });
  </script>
</body>
</html>
