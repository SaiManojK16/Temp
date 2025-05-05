<?php
$page_title = "Home | Tried & Tasted";
$current_page = 'home';
require_once '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Home | Tried & Tasted</title>
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

    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, #e67e22, #d35400);
      padding: 6rem 2rem;
      text-align: center;
      color: white;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('../assets/pattern.png');
      opacity: 0.1;
    }

    .hero-content {
      max-width: 1200px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .hero-content h1 {
      font-size: 3.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      line-height: 1.2;
    }

    .hero-content p {
      font-size: 1.2rem;
      margin-bottom: 2rem;
      opacity: 0.9;
      max-width: 600px;
    }

    .hero-buttons {
      display: flex;
      gap: 1rem;
    }

    .btn {
      padding: 1rem 2rem;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-primary {
      background: #fff;
      color: #e67e22;
      border: none;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(230, 126, 34, 0.2);
    }

    .btn-secondary {
      background: transparent;
      color: #fff;
      border: 2px solid #fff;
    }

    .btn-secondary:hover {
      background: rgba(255,255,255,0.1);
    }

    /* Features Section */
    .features {
      padding: 6rem 2rem;
      background: #fdf6ec;
    }

    .features-content {
      max-width: 1200px;
      margin: 0 auto;
    }

    .section-header {
      text-align: center;
      margin-bottom: 4rem;
      position: relative;
      padding-bottom: 2rem;
    }

    .section-header h2 {
      font-size: 2.5rem;
      color: #2c3e50;
      margin-bottom: 1rem;
    }

    .section-header p {
      color: #7f8c8d;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
    }

    .section-header::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: #e67e22;
      border-radius: 3px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }

    .feature-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(230, 126, 34, 0.1);
      transition: all 0.3s ease;
      border: 1px solid rgba(230, 126, 34, 0.1);
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 24px rgba(230, 126, 34, 0.15);
      border-color: rgba(230, 126, 34, 0.2);
    }

    .feature-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .feature-card:hover .feature-image {
      transform: scale(1.05);
    }

    .feature-content {
      padding: 2rem;
      background: linear-gradient(to bottom, #fff, #fdf6ec);
    }

    .feature-card h3 {
      font-size: 1.3rem;
      margin-bottom: 1rem;
      color: #e67e22;
    }

    .feature-card p {
      color: #7f8c8d;
      line-height: 1.6;
    }

    /* How It Works Section */
    .how-it-works {
      padding: 6rem 2rem;
      background: white;
    }

    .steps {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      position: relative;
    }

    .step {
      text-align: center;
      padding: 2rem;
      position: relative;
    }

    .step-number {
      width: 40px;
      height: 40px;
      background: #e67e22;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      margin: 0 auto 1.5rem;
      box-shadow: 0 4px 12px rgba(230, 126, 34, 0.2);
    }

    .step h3 {
      margin-bottom: 1rem;
      color: #2c3e50;
    }

    .step p {
      color: #7f8c8d;
    }

    /* CTA Section */
    .cta {
      padding: 6rem 2rem;
      background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
      color: white;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .cta::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('../assets/pattern.png');
      opacity: 0.1;
    }

    .cta-content {
      position: relative;
      z-index: 1;
    }

    .cta h2 {
      font-size: 2.5rem;
      margin-bottom: 1.5rem;
    }

    .cta p {
      font-size: 1.2rem;
      margin-bottom: 2rem;
      opacity: 0.9;
    }

    /* Footer */
    footer {
      background: #2c3e50;
      color: white;
      padding: 4rem 2rem;
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

    /* Additional Styles */
    .steps::before {
      content: '';
      position: absolute;
      top: 40px;
      left: 0;
      right: 0;
      height: 2px;
      background: #f1f2f6;
      z-index: 0;
    }

    @media (max-width: 768px) {
      .hero-content h1 {
        font-size: 2.5rem;
      }

      .hero-buttons {
        flex-direction: column;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }

      .steps::before {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="hero">
    <div class="hero-content">
      <h1>Discover Your Perfect Meal Plan</h1>
      <p>Get personalized recipe recommendations, plan your meals, and track your nutrition with our AI-powered meal planning platform.</p>
      <div class="hero-buttons">
        <a href="explore.php" class="btn btn-primary">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          Explore Recipes
        </a>
        <a href="meal_planner.php" class="btn btn-secondary">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zM16 2v4M8 2v4M3 10h18"/>
          </svg>
          Start Planning
        </a>
      </div>
    </div>
  </div>

  <section class="features">
    <div class="features-content">
      <div class="section-header">
        <h2>Why Choose Tried & Tasted?</h2>
        <p>Experience the future of meal planning with our innovative features</p>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-content">
            <h3>Smart Meal Planning</h3>
            <p>Create personalized weekly meal plans that match your dietary preferences and health goals. Our intuitive planner makes it easy to organize your meals.</p>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-content">
            <h3>Curated Recipe Collection</h3>
            <p>Access a diverse collection of recipes from various cuisines, carefully curated to ensure quality and variety in your meal planning.</p>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-content">
            <h3>Nutrition Tracking</h3>
            <p>Monitor your daily nutrition intake with detailed calorie and nutrient tracking to help you maintain a balanced diet.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="how-it-works">
    <div class="features-content">
      <div class="section-header">
        <h2>How It Works</h2>
        <p>Get started with Tried & Tasted in three simple steps</p>
      </div>
      <div class="steps">
        <div class="step">
          <div class="step-number">1</div>
          <h3>Create Your Profile</h3>
          <p>Set your dietary preferences and health goals</p>
        </div>
        <div class="step">
          <div class="step-number">2</div>
          <h3>Explore Recipes</h3>
          <p>Browse through our curated collection of recipes</p>
        </div>
        <div class="step">
          <div class="step-number">3</div>
          <h3>Plan Your Meals</h3>
          <p>Create your weekly meal plan and shopping list</p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-content">
      <h2>Ready to Transform Your Meal Planning?</h2>
      <p>Join thousands of users who have simplified their cooking and eating habits</p>
      <a href="explore.php" class="btn btn-primary">Get Started Now</a>
    </div>
  </section>

  <?php require_once '../includes/footer.php'; ?>
</body>
</html>
