  </main>
  <footer>
    <div class="footer-content">
      <div>
        <div class="footer-brand">Tried & Tasted</div>
        <p>AI Meal Planner, Personalized Recipes, Calorie Tracking & Smart Grocery.</p>
        <p>©️ 2025 Tried & Tasted. All rights reserved.</p>
      </div>
      <div class="footer-links">
        <h4>Quick Links</h4>
        <a href="explore.php">Explore</a>
        <a href="meal_planner.php">Meal Planner</a>
        <a href="saved_recipes.php">Saved Recipes</a>
        <a href="pantry.php">Pantry</a>
      </div>
      <div class="footer-links">
        <h4>Support</h4>
        <a href="#">Help Center</a>
        <a href="#">Contact Us</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
      </div>
    </div>
  </footer>

  <style>
    footer {
      background: #2c3e50;
      color: white;
      padding: 4rem 2rem;
      margin-top: 4rem;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 3rem;
    }

    .footer-brand {
      font-size: 1.5rem;
      font-weight: 600;
      color: #e67e22;
      margin-bottom: 1rem;
    }

    .footer-links h4 {
      color: #e67e22;
      margin-bottom: 1.5rem;
    }

    .footer-links a {
      color: #ecf0f1;
      text-decoration: none;
      display: block;
      margin-bottom: 0.75rem;
      transition: color 0.2s;
    }

    .footer-links a:hover {
      color: #e67e22;
    }

    @media (max-width: 768px) {
      footer {
        padding: 3rem 1rem;
      }
      
      .footer-content {
        gap: 2rem;
      }
    }
  </style>
</body>
</html>
