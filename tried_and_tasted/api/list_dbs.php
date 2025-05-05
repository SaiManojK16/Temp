
-- Create Database 

CREATE DATABASE ⁠ tried&tasted ⁠;



-- Users Table
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recipes Table
CREATE TABLE Recipes (
    recipe_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    servings INT NOT NULL,
    instructions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Ingredients Table
CREATE TABLE Ingredients (
    ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    calories DECIMAL(5,2) NOT NULL,
    serving_size_g DECIMAL(7,3) NOT NULL,
    measurement_unit VARCHAR(50) NOT NULL
);

-- RecipeIngredients Table (Junction)
CREATE TABLE RecipeIngredients (
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (recipe_id, ingredient_id),
    FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE CASCADE
);

-- MealPlan Table
CREATE TABLE MealPlan (
    meal_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    meal_date DATE NOT NULL,
    meal_time ENUM('Breakfast', 'Lunch', 'Dinner', 'Snack') NOT NULL,
    diet_preference ENUM('Veg', 'Non-Veg', 'Vegan', 'Only Fish') NOT NULL,
    allergy_info TEXT,
    skill_level ENUM('Beginner', 'Intermediate', 'Pro'),
    generated_by_ai BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- MealPlanRecipes Table (Junction)
CREATE TABLE MealPlanRecipes (
    meal_id INT NOT NULL,
    recipe_id INT NOT NULL,
    PRIMARY KEY (meal_id, recipe_id),
    FOREIGN KEY (meal_id) REFERENCES MealPlan(meal_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE
);

-- Favorites Table
CREATE TABLE Favorites (
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    favorited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, recipe_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE
);

-- ShoppingList Table (Header)
CREATE TABLE ShoppingList (
    list_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- ShoppingListDetails Table (contains)
CREATE TABLE ShoppingListDetails (
    list_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    measurement_unit VARCHAR(50),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (list_id, ingredient_id),
    FOREIGN KEY (list_id) REFERENCES ShoppingList(list_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE CASCADE
);

-- Pantry Table
CREATE TABLE Pantry (
    pantry_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    expiry_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE CASCADE
);

-- Consumption Table (logs)
CREATE TABLE Consumption (
    consumption_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    consumed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE
);

-- Users Table Inserts
INSERT INTO Users (full_name, email, password_hash) VALUES ('Ananya Sharma', 'ananya.sharma@example.com', 'hash1');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Rohan Mehta', 'rohan.mehta@example.com', 'hash2');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Priya Nair', 'priya.nair@example.com', 'hash3');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Arjun Reddy', 'arjun.reddy@example.com', 'hash4');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Sneha Iyer', 'sneha.iyer@example.com', 'hash5');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Rahul Khanna', 'rahul.khanna@example.com', 'hash6');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Divya Patel', 'divya.patel@example.com', 'hash7');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Karan Malhotra', 'karan.malhotra@example.com', 'hash8');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Neha Verma', 'neha.verma@example.com', 'hash9');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Amit Joshi', 'amit.joshi@example.com', 'hash10');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Ishita Roy', 'ishita.roy@example.com', 'hash11');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Manav Singh', 'manav.singh@example.com', 'hash12');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Tanvi Desai', 'tanvi.desai@example.com', 'hash13');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Siddharth Ghosh', 'siddharth.ghosh@example.com', 'hash14');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Meera Kumar', 'meera.kumar@example.com', 'hash15');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Kabir Chopra', 'kabir.chopra@example.com', 'hash16');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Ritika Shukla', 'ritika.shukla@example.com', 'hash17');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Aditya Rastogi', 'aditya.rastogi@example.com', 'hash18');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Pooja Bajaj', 'pooja.bajaj@example.com', 'hash19');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Nikhil Chaudhary', 'nikhil.chaudhary@example.com', 'hash20');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Simran Agarwal', 'simran.agarwal@example.com', 'hash21');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Raj Pandey', 'raj.pandey@example.com', 'hash22');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Shreya Bansal', 'shreya.bansal@example.com', 'hash23');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Varun Dubey', 'varun.dubey@example.com', 'hash24');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Naina Mishra', 'naina.mishra@example.com', 'hash25');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Kavya Rana', 'kavya.rana@example.com', 'hash26');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Vikram Trivedi', 'vikram.trivedi@example.com', 'hash27');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Alia Saxena', 'alia.saxena@example.com', 'hash28');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Dev Thakur', 'dev.thakur@example.com', 'hash29');
INSERT INTO Users (full_name, email, password_hash) VALUES ('Pari Kapoor', 'pari.kapoor@example.com', 'hash30');

-- Ingredients Table Inserts
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Basmati Rice', 142.77, 61.58, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Paneer', 172.68, 41.85, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Spinach', 70.05, 76.04, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Garam Masala', 40.91, 42.72, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Toor Dal', 167.01, 86.41, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Urad Dal', 79.49, 73.23, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Mustard Seeds', 153.04, 56.1, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Ghee', 139.15, 14.37, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Besan', 54.19, 34.34, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Tomato', 190.46, 34.99, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Green Chili', 77.2, 50.08, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Coriander', 63.57, 88.16, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Tamarind', 95.51, 85.87, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Cumin Seeds', 195.4, 71.85, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Red Chili Powder', 119.71, 56.03, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Turmeric', 108.49, 71.83, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Salt', 74.17, 90.74, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Oil', 103.55, 98.0, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Onion', 81.86, 26.77, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Garlic', 113.56, 10.87, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Ginger', 66.73, 10.79, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Curry Leaves', 122.13, 48.14, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Methi', 137.31, 63.06, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Carrot', 84.95, 44.37, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Peas', 133.05, 10.34, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Cauliflower', 144.7, 52.74, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Capsicum', 159.01, 24.73, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Yogurt', 48.06, 60.11, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Butter', 41.45, 57.21, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Coconut', 197.87, 98.4, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Jaggery', 32.42, 76.37, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Black Pepper', 187.74, 84.8, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Ajwain', 183.27, 47.55, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Cardamom', 39.39, 81.26, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Cloves', 53.83, 83.63, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Cinnamon', 127.55, 73.2, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Fenugreek', 34.67, 5.95, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Bay Leaf', 60.36, 44.01, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Asafoetida', 53.78, 13.19, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Sooji', 55.83, 18.0, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Rava', 124.92, 99.47, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Poha', 70.39, 38.32, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Sabudana', 108.69, 59.92, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Murmura', 90.54, 21.03, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Moong Dal', 62.74, 39.46, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Masoor Dal', 111.9, 87.52, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Chana Dal', 195.31, 42.5, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Chickpeas', 156.74, 79.9, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Spring Onion', 76.68, 16.65, 'grams');
INSERT INTO Ingredients (name, calories, serving_size_g, measurement_unit) VALUES ('Mint', 104.14, 64.6, 'grams');

-- Recipes Table Inserts
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (1, 'Masala Dosa', 3, 'Instructions for Masala Dosa.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (2, 'Palak Paneer', 2, 'Instructions for Palak Paneer.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (3, 'Chole Bhature', 2, 'Instructions for Chole Bhature.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (4, 'Butter Chicken', 4, 'Instructions for Butter Chicken.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (5, 'Biryani', 5, 'Instructions for Biryani.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (6, 'Rajma Chawal', 3, 'Instructions for Rajma Chawal.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (7, 'Aloo Paratha', 4, 'Instructions for Aloo Paratha.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (8, 'Sambar', 4, 'Instructions for Sambar.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (9, 'Pav Bhaji', 2, 'Instructions for Pav Bhaji.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (10, 'Kadai Paneer', 5, 'Instructions for Kadai Paneer.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (11, 'Tandoori Chicken', 5, 'Instructions for Tandoori Chicken.');
INSERT INTO Recipes (user_id, title, servings, instructions) VALUES (12, 'Dhokla', 3, 'Instructions for Dhokla.');

-- RecipeIngredients Table Inserts
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (1, 31, 54.75);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (1, 36, 31.17);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (1, 8, 14.15);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (2, 34, 58.72);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (2, 40, 32.95);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (2, 47, 77.26);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (3, 34, 37.99);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (3, 41, 31.73);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (3, 18, 16.38);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (4, 44, 51.4);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (4, 34, 19.04);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (4, 40, 75.01);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (5, 39, 54.4);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (5, 49, 63.32);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (5, 18, 83.51);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (6, 10, 94.86);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (6, 41, 12.47);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (6, 22, 96.43);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (7, 22, 35.46);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (7, 1, 24.21);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (7, 13, 40.63);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (8, 9, 52.33);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (8, 16, 86.41);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (8, 30, 11.75);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (9, 12, 32.62);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (9, 25, 81.44);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (9, 17, 98.48);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (10, 11, 34.46);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (10, 9, 80.21);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (10, 23, 14.61);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (11, 38, 48.63);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (11, 8, 65.89);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (11, 27, 15.65);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (12, 10, 66.4);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (12, 15, 46.13);
INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES (12, 18, 33.61);

-- MealPlan Table Inserts
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (1, '2025-04-01', 'Breakfast', 'Veg', 'None', 'Intermediate', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (2, '2025-04-02', 'Breakfast', 'Veg', 'None', 'Beginner', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (3, '2025-04-03', 'Breakfast', 'Non-Veg', 'None', 'Beginner', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (4, '2025-04-04', 'Breakfast', 'Veg', 'None', 'Intermediate', FALSE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (5, '2025-04-05', 'Breakfast', 'Veg', 'None', 'Intermediate', FALSE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (6, '2025-04-06', 'Lunch', 'Vegan', 'None', 'Beginner', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (7, '2025-04-07', 'Dinner', 'Veg', 'None', 'Beginner', FALSE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (8, '2025-04-08', 'Lunch', 'Veg', 'None', 'Pro', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (9, '2025-04-09', 'Dinner', 'Vegan', 'None', 'Intermediate', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (10, '2025-04-10', 'Breakfast', 'Non-Veg', 'None', 'Beginner', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (11, '2025-04-11', 'Breakfast', 'Vegan', 'None', 'Pro', TRUE);
INSERT INTO MealPlan (user_id, meal_date, meal_time, diet_preference, allergy_info, skill_level, generated_by_ai)
    VALUES (12, '2025-04-12', 'Lunch', 'Vegan', 'None', 'Intermediate', FALSE);

-- MealPlanRecipes Table Inserts
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (1, 9);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (2, 2);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (2, 4);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (2, 8);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (3, 8);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (3, 5);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (4, 4);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (5, 4);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (5, 10);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (5, 1);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (6, 1);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (6, 4);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (6, 11);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (7, 12);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (7, 10);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (8, 9);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (8, 1);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (9, 8);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (9, 9);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (10, 6);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (10, 4);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (10, 11);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (11, 4);
INSERT INTO MealPlanRecipes (meal_id, recipe_id) VALUES (12, 11);

-- Favorites Table Inserts
INSERT INTO Favorites (user_id, recipe_id) VALUES (1, 10);
INSERT INTO Favorites (user_id, recipe_id) VALUES (1, 11);
INSERT INTO Favorites (user_id, recipe_id) VALUES (1, 3);
INSERT INTO Favorites (user_id, recipe_id) VALUES (2, 11);
INSERT INTO Favorites (user_id, recipe_id) VALUES (2, 2);
INSERT INTO Favorites (user_id, recipe_id) VALUES (2, 1);
INSERT INTO Favorites (user_id, recipe_id) VALUES (2, 5);
INSERT INTO Favorites (user_id, recipe_id) VALUES (3, 5);
INSERT INTO Favorites (user_id, recipe_id) VALUES (3, 7);
INSERT INTO Favorites (user_id, recipe_id) VALUES (4, 5);
INSERT INTO Favorites (user_id, recipe_id) VALUES (4, 7);
INSERT INTO Favorites (user_id, recipe_id) VALUES (4, 9);
INSERT INTO Favorites (user_id, recipe_id) VALUES (4, 10);
INSERT INTO Favorites (user_id, recipe_id) VALUES (7, 2);
INSERT INTO Favorites (user_id, recipe_id) VALUES (8, 2);
INSERT INTO Favorites (user_id, recipe_id) VALUES (8, 10);
INSERT INTO Favorites (user_id, recipe_id) VALUES (8, 8);
INSERT INTO Favorites (user_id, recipe_id) VALUES (9, 6);
INSERT INTO Favorites (user_id, recipe_id) VALUES (9, 3);
INSERT INTO Favorites (user_id, recipe_id) VALUES (10, 4);
INSERT INTO Favorites (user_id, recipe_id) VALUES (10, 11);
INSERT INTO Favorites (user_id, recipe_id) VALUES (10, 1);
INSERT INTO Favorites (user_id, recipe_id) VALUES (11, 1);
INSERT INTO Favorites (user_id, recipe_id) VALUES (11, 8);
INSERT INTO Favorites (user_id, recipe_id) VALUES (11, 5);
INSERT INTO Favorites (user_id, recipe_id) VALUES (11, 2);
INSERT INTO Favorites (user_id, recipe_id) VALUES (13, 4);
INSERT INTO Favorites (user_id, recipe_id) VALUES (13, 7);
INSERT INTO Favorites (user_id, recipe_id) VALUES (13, 5);
INSERT INTO Favorites (user_id, recipe_id) VALUES (15, 7);
INSERT INTO Favorites (user_id, recipe_id) VALUES (15, 8);
INSERT INTO Favorites (user_id, recipe_id) VALUES (15, 3);
INSERT INTO Favorites (user_id, recipe_id) VALUES (17, 4);
INSERT INTO Favorites (user_id, recipe_id) VALUES (17, 2);
INSERT INTO Favorites (user_id, recipe_id) VALUES (17, 6);
INSERT INTO Favorites (user_id, recipe_id) VALUES (19, 4);
INSERT INTO Favorites (user_id, recipe_id) VALUES (19, 6);
INSERT INTO Favorites (user_id, recipe_id) VALUES (22, 10);
INSERT INTO Favorites (user_id, recipe_id) VALUES (22, 12);
INSERT INTO Favorites (user_id, recipe_id) VALUES (23, 9);
INSERT INTO Favorites (user_id, recipe_id) VALUES (23, 4);
INSERT INTO Favorites (user_id, recipe_id) VALUES (25, 6);
INSERT INTO Favorites (user_id, recipe_id) VALUES (25, 7);
INSERT INTO Favorites (user_id, recipe_id) VALUES (25, 3);
INSERT INTO Favorites (user_id, recipe_id) VALUES (25, 12);
INSERT INTO Favorites (user_id, recipe_id) VALUES (26, 1);
INSERT INTO Favorites (user_id, recipe_id) VALUES (27, 1);
INSERT INTO Favorites (user_id, recipe_id) VALUES (27, 10);
INSERT INTO Favorites (user_id, recipe_id) VALUES (27, 9);
INSERT INTO Favorites (user_id, recipe_id) VALUES (28, 10);
INSERT INTO Favorites (user_id, recipe_id) VALUES (28, 9);
INSERT INTO Favorites (user_id, recipe_id) VALUES (29, 1);
INSERT INTO Favorites (user_id, recipe_id) VALUES (30, 2);
INSERT INTO Favorites (user_id, recipe_id) VALUES (30, 3);
INSERT INTO Favorites (user_id, recipe_id) VALUES (30, 1);

-- ShoppingList Table Inserts (one list per user)
INSERT INTO ShoppingList (user_id) VALUES (1);
INSERT INTO ShoppingList (user_id) VALUES (2);
INSERT INTO ShoppingList (user_id) VALUES (3);
INSERT INTO ShoppingList (user_id) VALUES (4);
INSERT INTO ShoppingList (user_id) VALUES (5);
INSERT INTO ShoppingList (user_id) VALUES (6);
INSERT INTO ShoppingList (user_id) VALUES (7);
INSERT INTO ShoppingList (user_id) VALUES (8);
INSERT INTO ShoppingList (user_id) VALUES (9);
INSERT INTO ShoppingList (user_id) VALUES (10);
INSERT INTO ShoppingList (user_id) VALUES (11);
INSERT INTO ShoppingList (user_id) VALUES (12);
INSERT INTO ShoppingList (user_id) VALUES (13);
INSERT INTO ShoppingList (user_id) VALUES (14);
INSERT INTO ShoppingList (user_id) VALUES (15);
INSERT INTO ShoppingList (user_id) VALUES (16);
INSERT INTO ShoppingList (user_id) VALUES (17);
INSERT INTO ShoppingList (user_id) VALUES (18);
INSERT INTO ShoppingList (user_id) VALUES (19);
INSERT INTO ShoppingList (user_id) VALUES (20);
INSERT INTO ShoppingList (user_id) VALUES (21);
INSERT INTO ShoppingList (user_id) VALUES (22);
INSERT INTO ShoppingList (user_id) VALUES (23);
INSERT INTO ShoppingList (user_id) VALUES (24);
INSERT INTO ShoppingList (user_id) VALUES (25);
INSERT INTO ShoppingList (user_id) VALUES (26);
INSERT INTO ShoppingList (user_id) VALUES (27);
INSERT INTO ShoppingList (user_id) VALUES (28);
INSERT INTO ShoppingList (user_id) VALUES (29);
INSERT INTO ShoppingList (user_id) VALUES (30);

-- ShoppingListDetails Inserts
-- We ensure that each shopping list (list_id) has at least two detail entries.
-- (For lists with only one original row, we add an extra row.)
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (1, 29, 274.23, 'grams');
-- Added a second detail for list_id 1:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (1, 5, 150.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (2, 30, 117.88, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (2, 39, 95.73, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (2, 42, 201.54, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (2, 1, 109.16, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (3, 23, 69.43, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (3, 37, 230.18, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (3, 13, 235.05, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (3, 48, 285.7, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (4, 21, 91.54, 'grams');
-- Added a second detail for list_id 4:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (4, 9, 80.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (5, 19, 297.81, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (5, 24, 156.31, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (5, 10, 123.11, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (6, 24, 57.85, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (6, 14, 51.21, 'grams');
-- Added a second detail for list_id 6:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (6, 8, 75.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (7, 39, 165.59, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (7, 45, 280.98, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (7, 14, 49.53, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (7, 4, 212.86, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (8, 42, 91.05, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (8, 46, 275.11, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (8, 47, 254.66, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (9, 38, 246.07, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (9, 33, 85.16, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (10, 37, 160.4, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (10, 4, 276.5, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (10, 3, 118.82, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (10, 16, 131.66, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (11, 18, 271.42, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (11, 10, 178.65, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (12, 9, 23.91, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (12, 32, 34.41, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (13, 36, 92.53, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (13, 3, 70.6, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (14, 21, 241.79, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (14, 29, 266.05, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (14, 39, 208.64, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (14, 23, 115.98, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (15, 37, 251.18, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (15, 27, 270.16, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (15, 14, 83.42, 'grams');
-- Added a second detail for list_id 15:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (15, 5, 120.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (16, 22, 281.51, 'grams');
-- Added a second detail for list_id 16:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (16, 9, 100.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (17, 16, 40.59, 'grams');
-- Added a second detail for list_id 17:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (17, 4, 95.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (18, 8, 90.16, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (18, 20, 171.3, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (18, 3, 249.89, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (18, 23, 174.43, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (19, 42, 21.51, 'grams');
-- Added a second detail for list_id 19:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (19, 1, 110.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (20, 1, 258.6, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (20, 8, 128.71, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (20, 31, 289.45, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (20, 9, 198.88, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (21, 27, 47.86, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (21, 16, 144.03, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (22, 4, 193.19, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (22, 14, 156.61, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (22, 15, 233.71, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (23, 36, 254.18, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (23, 29, 214.87, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (24, 34, 283.6, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (24, 23, 270.63, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (24, 49, 287.71, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (25, 30, 260.78, 'grams');
-- Added a second detail for list_id 25:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (25, 7, 140.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (26, 31, 176.91, 'grams');
-- Added a second detail for list_id 26:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (26, 9, 130.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (27, 22, 188.81, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (27, 32, 168.3, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (27, 38, 273.7, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (27, 1, 152.2, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (28, 19, 217.54, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (28, 9, 62.96, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (28, 27, 172.8, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (28, 31, 249.43, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (29, 26, 65.1, 'grams');
-- Added a second detail for list_id 29:
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (29, 4, 90.00, 'grams');

INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (30, 49, 239.09, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (30, 11, 115.52, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (30, 22, 158.52, 'grams');
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
                VALUES (30, 29, 135.52, 'grams');

-- Example Pantry Inserts with varying numbers of items per user

-- User 1: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (1, 1, 100.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (1, 5, 200.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (1, 10, 150.00, '2025-06-30');

-- User 2: 1 item
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (2, 3, 80.00, '2025-06-30');

-- User 3: 4 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (3, 2, 90.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (3, 7, 70.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (3, 8, 60.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (3, 9, 110.00, '2025-06-30');

-- User 4: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (4, 4, 120.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (4, 12, 130.00, '2025-06-30');

-- User 5: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (5, 6, 85.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (5, 11, 95.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (5, 15, 105.00, '2025-06-30');

-- User 6: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (6, 14, 150.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (6, 16, 140.00, '2025-06-30');

-- User 7: 4 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (7, 17, 120.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (7, 18, 130.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (7, 19, 140.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (7, 20, 110.00, '2025-06-30');

-- User 8: 1 item
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (8, 21, 160.00, '2025-06-30');

-- User 9: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (9, 22, 170.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (9, 23, 180.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (9, 24, 190.00, '2025-06-30');

-- User 10: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (10, 25, 200.00, '2025-06-30');

-- User 11: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (11, 27, 220.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (11, 28, 230.00, '2025-06-30');

-- User 12: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (12, 29, 240.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (12, 30, 250.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (12, 31, 260.00, '2025-06-30');

-- User 13: 1 item
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (13, 32, 270.00, '2025-06-30');

-- User 14: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (14, 33, 280.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (14, 34, 290.00, '2025-06-30');

-- User 15: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (15, 35, 300.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (15, 36, 310.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (15, 37, 320.00, '2025-06-30');

-- User 16: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (16, 38, 330.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (16, 39, 340.00, '2025-06-30');

-- User 17: 1 item
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (17, 40, 350.00, '2025-06-30');

-- User 18: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (18, 41, 360.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (18, 42, 370.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (18, 43, 380.00, '2025-06-30');

-- User 19: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (19, 44, 390.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (19, 45, 400.00, '2025-06-30');

-- User 20: 4 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (20, 46, 410.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (20, 47, 420.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (20, 48, 430.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (20, 49, 440.00, '2025-06-30');

-- User 21: 1 item
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (21, 50, 450.00, '2025-06-30');

-- User 22: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (22, 1, 460.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (22, 2, 470.00, '2025-06-30');

-- User 23: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (23, 3, 480.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (23, 4, 490.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (23, 5, 500.00, '2025-06-30');

-- User 24: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (24, 6, 510.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (24, 7, 520.00, '2025-06-30');

-- User 25: 1 item
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (25, 8, 530.00, '2025-06-30');

-- User 26: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (26, 9, 540.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (26, 10, 550.00, '2025-06-30');

-- User 27: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (27, 11, 560.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (27, 12, 570.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (27, 13, 580.00, '2025-06-30');

-- User 28: 2 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (28, 14, 590.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (28, 15, 600.00, '2025-06-30');

-- User 29: 1 item
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (29, 16, 610.00, '2025-06-30');

-- User 30: 3 items
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (30, 17, 620.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (30, 18, 630.00, '2025-06-30');
INSERT INTO Pantry (user_id, ingredient_id, quantity, expiry_date)
  VALUES (30, 19, 640.00, '2025-06-30');

-- Consumption Table Inserts
INSERT INTO Consumption (user_id, recipe_id) VALUES (1, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (1, 9);
INSERT INTO Consumption (user_id, recipe_id) VALUES (1, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (2, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (2, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (2, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (2, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (3, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (3, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (3, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (3, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (3, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (4, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (4, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (4, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (4, 12);
INSERT INTO Consumption (user_id, recipe_id) VALUES (5, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (5, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (5, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (5, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (5, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (6, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (6, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (6, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (7, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (7, 12);
INSERT INTO Consumption (user_id, recipe_id) VALUES (7, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (7, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (8, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (8, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (8, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (8, 12);
INSERT INTO Consumption (user_id, recipe_id) VALUES (9, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (9, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (9, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (9, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (9, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (10, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (10, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (10, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (10, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (10, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (11, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (11, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (11, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (11, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (11, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (12, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (13, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (13, 9);
INSERT INTO Consumption (user_id, recipe_id) VALUES (13, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (13, 12);
INSERT INTO Consumption (user_id, recipe_id) VALUES (13, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (14, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (15, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (15, 9);
INSERT INTO Consumption (user_id, recipe_id) VALUES (15, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (16, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (17, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (17, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (18, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (18, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (18, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (18, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (19, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (19, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (19, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (20, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (20, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (20, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (20, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (20, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (21, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (21, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (21, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (22, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (22, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (22, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (23, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (23, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (24, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (24, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (24, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (24, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (24, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (25, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (25, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (25, 3);
INSERT INTO Consumption (user_id, recipe_id) VALUES (25, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (26, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (26, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (26, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (26, 6);
INSERT INTO Consumption (user_id, recipe_id) VALUES (26, 4);
INSERT INTO Consumption (user_id, recipe_id) VALUES (27, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (27, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (27, 1);
INSERT INTO Consumption (user_id, recipe_id) VALUES (27, 11);
INSERT INTO Consumption (user_id, recipe_id) VALUES (27, 12);
INSERT INTO Consumption (user_id, recipe_id) VALUES (28, 9);
INSERT INTO Consumption (user_id, recipe_id) VALUES (28, 7);
INSERT INTO Consumption (user_id, recipe_id) VALUES (28, 10);
INSERT INTO Consumption (user_id, recipe_id) VALUES (29, 12);
INSERT INTO Consumption (user_id, recipe_id) VALUES (29, 8);
INSERT INTO Consumption (user_id, recipe_id) VALUES (29, 9);
INSERT INTO Consumption (user_id, recipe_id) VALUES (30, 12);
INSERT INTO Consumption (user_id, recipe_id) VALUES (30, 5);
INSERT INTO Consumption (user_id, recipe_id) VALUES (30, 2);
INSERT INTO Consumption (user_id, recipe_id) VALUES (30, 1);


-- Procedure

CREATE PROCEDURE update_shopping_list(IN userId INT)
INSERT INTO ShoppingListDetails (list_id, ingredient_id, quantity, measurement_unit)
SELECT sl.list_id, p.ingredient_id, (5.0 - p.quantity) AS needed_qty, i.measurement_unit
FROM Pantry p
JOIN Ingredients i ON p.ingredient_id = i.ingredient_id
JOIN ShoppingList sl ON p.user_id = sl.user_id
WHERE p.user_id = userId AND p.quantity < 5.0;
-- testing procedure
SELECT * FROM Pantry
WHERE user_id = 3;

UPDATE Pantry
SET quantity = 3.0
WHERE user_id = 3 AND ingredient_id = 8;

SELECT * 
FROM ShoppingListDetails
WHERE list_id = (SELECT list_id FROM ShoppingList WHERE user_id = 3);

CALL update_shopping_list(3);

SELECT * 
FROM ShoppingListDetails
WHERE list_id IN (SELECT list_id FROM ShoppingList WHERE user_id = 3);


-- Trigger
CREATE TRIGGER after_consumption_deduct_pantry
AFTER INSERT ON Consumption
FOR EACH ROW
UPDATE Pantry p
JOIN RecipeIngredients ri ON p.ingredient_id = ri.ingredient_id
SET p.quantity = p.quantity - ri.quantity
WHERE ri.recipe_id = NEW.recipe_id
  AND p.user_id = NEW.user_id;

-- Testing triger
SELECT * FROM Pantry WHERE user_id = 3;
SELECT * FROM RecipeIngredients WHERE recipe_id = 1;
SELECT * FROM Pantry WHERE user_id = 3 AND ingredient_id = 8;
INSERT INTO Consumption (user_id, recipe_id) VALUES (3, 1);
SELECT * FROM Pantry WHERE user_id = 3 AND ingredient_id = 8;
