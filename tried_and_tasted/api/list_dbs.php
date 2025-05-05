
-- Create Database 

CREATE DATABASE ⁠ tried&tasted ⁠;
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: May 05, 2025 at 01:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_meal_to_plan` (IN `p_plan_id` INT, IN `p_meal_date` DATE, IN `p_meal_time` VARCHAR(20), IN `p_user_id` INT)   BEGIN
    DECLARE v_recipe_id INT;
    DECLARE v_calories INT;
    
    -- Find a recipe that hasn't been used in the last 2 days
    SELECT 
        r.recipe_id,
        r.total_calories
    INTO 
        v_recipe_id,
        v_calories
    FROM Recipes r
    WHERE r.user_id = p_user_id
    AND NOT EXISTS (
        SELECT 1 
        FROM meal_plan_entry mpe
        JOIN meal_plan mp ON mpe.plan_id = mp.plan_id
        WHERE mp.user_id = p_user_id
        AND mpe.recipe_id = r.recipe_id
        AND mpe.meal_date >= DATE_SUB(p_meal_date, INTERVAL 2 DAY)
    )
    ORDER BY RAND()
    LIMIT 1;
    
    -- If recipe found, add to meal plan
    IF v_recipe_id IS NOT NULL THEN
        INSERT INTO meal_plan_entry (
            plan_id,
            meal_date,
            meal_time,
            recipe_id,
            calories,
            status
        ) VALUES (
            p_plan_id,
            p_meal_date,
            p_meal_time,
            v_recipe_id,
            v_calories,
            'Planned'
        );
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_simple_meal_plan` (IN `p_user_id` INT, IN `p_week_start` DATE, IN `p_include_breakfast` BOOLEAN, IN `p_include_lunch` BOOLEAN, IN `p_include_dinner` BOOLEAN)   BEGIN
    DECLARE v_plan_id INT;
    DECLARE v_current_date DATE;
    DECLARE v_meal_time VARCHAR(20);
    DECLARE v_recipe_id INT;
    DECLARE v_calories INT;
    
    -- Create new meal plan
    INSERT INTO meal_plan (user_id, week_start)
    VALUES (p_user_id, p_week_start);
    SET v_plan_id = LAST_INSERT_ID();
    
    -- Set initial date
    SET v_current_date = p_week_start;
    
    -- Loop through each day of the week
    WHILE v_current_date < DATE_ADD(p_week_start, INTERVAL 7 DAY) DO
        -- Loop through meal times based on user preferences
        IF p_include_breakfast THEN
            SET v_meal_time = 'Breakfast';
            CALL add_meal_to_plan(v_plan_id, v_current_date, v_meal_time, p_user_id);
        END IF;
        
        IF p_include_lunch THEN
            SET v_meal_time = 'Lunch';
            CALL add_meal_to_plan(v_plan_id, v_current_date, v_meal_time, p_user_id);
        END IF;
        
        IF p_include_dinner THEN
            SET v_meal_time = 'Dinner';
            CALL add_meal_to_plan(v_plan_id, v_current_date, v_meal_time, p_user_id);
        END IF;
        
        -- Move to next day
        SET v_current_date = DATE_ADD(v_current_date, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `consumption`
--

CREATE TABLE `consumption` (
  `consumption_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `consumed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `consumption`
--
DELIMITER $$
CREATE TRIGGER `after_consumption_deduct_pantry` AFTER INSERT ON `consumption` FOR EACH ROW UPDATE Pantry p
JOIN RecipeIngredients ri ON p.ingredient_id = ri.ingredient_id
SET p.quantity = p.quantity - ri.quantity
WHERE ri.recipe_id = NEW.recipe_id
  AND p.user_id = NEW.user_id
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `favorited_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`user_id`, `recipe_id`, `favorited_at`) VALUES
(1, 57, '2025-05-05 02:25:24'),
(1, 58, '2025-05-04 23:03:47'),
(1, 60, '2025-05-05 05:58:52');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `calories` decimal(5,2) NOT NULL,
  `serving_size_g` decimal(7,3) NOT NULL,
  `measurement_unit` varchar(50) NOT NULL,
  `ingredient_category` enum('Grain','Dairy','Nuts','Vegetable','Fruit','Meat','Seafood','Spice','Oil','Legume','Sweetener','Gluten','Soy','Egg','Beverage','Other') DEFAULT 'Other'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `name`, `calories`, `serving_size_g`, `measurement_unit`, `ingredient_category`) VALUES
(601, 'Poha', 130.00, 100.000, 'g', 'Grain'),
(602, 'Mustard Seeds', 508.00, 100.000, 'g', 'Spice'),
(603, 'Onion', 40.00, 100.000, 'g', 'Vegetable'),
(604, 'Rava', 360.00, 100.000, 'g', 'Grain'),
(605, 'Urad Dal', 347.00, 100.000, 'g', 'Legume'),
(606, 'Green Chilies', 40.00, 100.000, 'g', 'Vegetable'),
(607, 'Wheat Flour', 340.00, 100.000, 'g', 'Grain'),
(608, 'Potatoes', 77.00, 100.000, 'g', 'Vegetable'),
(609, 'Spices', 250.00, 50.000, 'g', 'Spice'),
(610, 'Rice', 130.00, 100.000, 'g', 'Grain'),
(611, 'Salt', 0.00, 5.000, 'g', 'Other'),
(612, 'Besan', 387.00, 100.000, 'g', 'Legume'),
(613, 'Yogurt', 59.00, 100.000, 'g', 'Dairy'),
(614, 'ENO', 0.00, 5.000, 'g', 'Other'),
(615, 'Vegetables', 60.00, 100.000, 'g', 'Vegetable'),
(616, 'Oil', 884.00, 100.000, 'g', 'Oil'),
(617, 'Bread', 265.00, 100.000, 'g', 'Grain'),
(618, 'Butter', 717.00, 100.000, 'g', 'Dairy'),
(619, 'Sabudana', 358.00, 100.000, 'g', 'Grain'),
(620, 'Peanuts', 567.00, 100.000, 'g', 'Nuts'),
(621, 'Cumin', 375.00, 100.000, 'g', 'Spice'),
(622, 'Moong Dal', 347.00, 100.000, 'g', 'Legume'),
(623, 'Methi', 49.00, 100.000, 'g', 'Vegetable'),
(624, 'Paneer', 296.00, 100.000, 'g', 'Dairy'),
(625, 'Tomato', 18.00, 100.000, 'g', 'Vegetable'),
(626, 'Cream', 340.00, 50.000, 'g', 'Dairy'),
(627, 'Chicken', 239.00, 100.000, 'g', 'Meat'),
(628, 'Garlic', 149.00, 10.000, 'g', 'Vegetable'),
(629, 'Ginger', 80.00, 10.000, 'g', 'Vegetable'),
(630, 'Basmati Rice', 120.00, 100.000, 'g', 'Grain'),
(631, 'Saffron', 310.00, 1.000, 'g', 'Spice'),
(632, 'Fish', 206.00, 100.000, 'g', 'Seafood'),
(633, 'Coconut Milk', 230.00, 100.000, 'ml', 'Other'),
(634, 'Coriander', 23.00, 10.000, 'g', 'Vegetable'),
(635, 'Rajma', 337.00, 100.000, 'g', 'Legume'),
(636, 'Eggs', 155.00, 1.000, 'piece', 'Egg'),
(637, 'Chickpeas', 364.00, 100.000, 'g', 'Legume'),
(638, 'Peas', 81.00, 100.000, 'g', 'Vegetable'),
(639, 'Ginger Garlic Paste', 120.00, 20.000, 'g', 'Other'),
(640, 'Spinach', 23.00, 100.000, 'g', 'Vegetable'),
(641, 'Mutton', 294.00, 100.000, 'g', 'Meat'),
(642, 'Brinjal', 25.00, 100.000, 'g', 'Vegetable'),
(643, 'Semolina', 360.00, 50.000, 'g', 'Grain'),
(644, 'Red Chilies', 282.00, 5.000, 'g', 'Spice'),
(645, 'Coconut Paste', 350.00, 50.000, 'g', 'Other'),
(646, 'Tofu', 144.00, 100.000, 'g', 'Soy'),
(665, 'Avocado', 160.00, 100.000, 'g', 'Fruit'),
(666, 'Basil', 23.00, 10.000, 'g', 'Vegetable'),
(667, 'Bell Peppers', 31.00, 100.000, 'g', 'Vegetable'),
(668, 'Chopped Vegetables', 60.00, 100.000, 'g', 'Vegetable'),
(669, 'Cucumber', 16.00, 100.000, 'g', 'Vegetable'),
(670, 'Feta Cheese', 264.00, 100.000, 'g', 'Dairy'),
(671, 'Greek Yogurt', 59.00, 100.000, 'g', 'Dairy'),
(672, 'Herbs', 40.00, 10.000, 'g', 'Other'),
(673, 'Honey', 304.00, 100.000, 'g', 'Sweetener'),
(674, 'Hummus', 166.00, 100.000, 'g', 'Other'),
(675, 'Labneh', 110.00, 100.000, 'g', 'Dairy'),
(676, 'Lemon', 29.00, 100.000, 'g', 'Fruit'),
(677, 'Nuts', 607.00, 100.000, 'g', 'Nuts'),
(678, 'Olive Oil', 884.00, 100.000, 'g', 'Oil'),
(679, 'Olives', 115.00, 100.000, 'g', 'Other'),
(680, 'Pita Bread', 275.00, 100.000, 'g', 'Grain'),
(681, 'Za\'atar', 320.00, 10.000, 'g', 'Spice'),
(682, 'Quinoa', 120.00, 100.000, 'g', 'Grain'),
(683, 'Falafel', 333.00, 100.000, 'g', 'Legume'),
(684, 'Tahini', 595.00, 100.000, 'g', 'Other'),
(685, 'Orzo', 357.00, 100.000, 'g', 'Grain'),
(686, 'Lettuce', 15.00, 100.000, 'g', 'Vegetable'),
(687, 'Tuna', 132.00, 100.000, 'g', 'Seafood'),
(688, 'Boiled Eggs', 155.00, 1.000, 'piece', 'Egg'),
(689, 'Capers', 23.00, 100.000, 'g', 'Other'),
(690, 'Green Beans', 31.00, 100.000, 'g', 'Vegetable'),
(691, 'Parsley', 36.00, 50.000, 'g', 'Vegetable'),
(692, 'Lentils', 116.00, 100.000, 'g', 'Legume'),
(693, 'Red Wine Vinegar', 6.00, 15.000, 'ml', 'Other'),
(694, 'Mushrooms', 22.00, 100.000, 'g', 'Vegetable'),
(695, 'Zucchini', 17.00, 100.000, 'g', 'Vegetable'),
(696, 'Couscous', 112.00, 100.000, 'g', 'Grain'),
(697, 'Sundried Tomatoes', 258.00, 100.000, 'g', 'Vegetable'),
(698, 'Cannelloni Pasta', 350.00, 100.000, 'g', 'Grain'),
(699, 'Seafood Mix', 105.00, 100.000, 'g', 'Seafood'),
(700, 'Parmesan Cheese', 431.00, 100.000, 'g', 'Dairy'),
(701, 'Tomato Sauce', 29.00, 100.000, 'g', 'Vegetable'),
(702, 'Lamb Chops', 294.00, 100.000, 'g', 'Meat'),
(703, 'Paprika', 282.00, 10.000, 'g', 'Spice'),
(704, 'Oregano', 265.00, 10.000, 'g', 'Spice'),
(705, 'briyani', 0.00, 0.000, '', 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `mealplan`
--

CREATE TABLE `mealplan` (
  `meal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_time` enum('Breakfast','Lunch','Dinner','Snack') NOT NULL,
  `diet_preference` enum('Veg','Non-Veg','Vegan','Only Fish') NOT NULL,
  `allergy_info` text DEFAULT NULL,
  `skill_level` enum('Beginner','Intermediate','Pro') DEFAULT NULL,
  `generated_by_ai` tinyint(1) DEFAULT 1,
  `calories_consumed` decimal(5,2) DEFAULT 0.00,
  `status` enum('Completed','Skipped','Substituted') DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mealplan`
--

INSERT INTO `mealplan` (`meal_id`, `user_id`, `meal_date`, `meal_time`, `diet_preference`, `allergy_info`, `skill_level`, `generated_by_ai`, `calories_consumed`, `status`) VALUES
(1, 1, '2025-04-01', 'Breakfast', 'Veg', 'None', 'Intermediate', 1, 0.00, 'Completed'),
(2, 2, '2025-04-02', 'Breakfast', 'Veg', 'None', 'Beginner', 1, 0.00, 'Completed'),
(3, 3, '2025-04-03', 'Breakfast', 'Non-Veg', 'None', 'Beginner', 1, 0.00, 'Completed'),
(4, 4, '2025-04-04', 'Breakfast', 'Veg', 'None', 'Intermediate', 0, 0.00, 'Completed'),
(5, 5, '2025-04-05', 'Breakfast', 'Veg', 'None', 'Intermediate', 0, 0.00, 'Completed'),
(6, 6, '2025-04-06', 'Lunch', 'Vegan', 'None', 'Beginner', 1, 0.00, 'Completed'),
(7, 7, '2025-04-07', 'Dinner', 'Veg', 'None', 'Beginner', 0, 0.00, 'Completed'),
(8, 8, '2025-04-08', 'Lunch', 'Veg', 'None', 'Pro', 1, 0.00, 'Completed'),
(9, 9, '2025-04-09', 'Dinner', 'Vegan', 'None', 'Intermediate', 1, 0.00, 'Completed'),
(10, 10, '2025-04-10', 'Breakfast', 'Non-Veg', 'None', 'Beginner', 1, 0.00, 'Completed'),
(11, 11, '2025-04-11', 'Breakfast', 'Vegan', 'None', 'Pro', 1, 0.00, 'Completed'),
(12, 12, '2025-04-12', 'Lunch', 'Vegan', 'None', 'Intermediate', 0, 0.00, 'Completed'),
(13, 1, '2025-05-04', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(14, 1, '2025-05-04', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(15, 1, '2025-01-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(16, 1, '2025-01-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(17, 1, '2001-09-12', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(18, 1, '2001-09-12', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(19, 2, '2001-09-12', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(20, 1, '2025-04-12', 'Breakfast', 'Veg', NULL, NULL, 1, 700.00, 'Completed'),
(21, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(22, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(23, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(24, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(25, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(26, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(27, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(28, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(29, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(30, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed'),
(31, 1, '2025-05-05', 'Breakfast', 'Veg', NULL, NULL, 1, 0.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `mealplanrecipes`
--

CREATE TABLE `mealplanrecipes` (
  `meal_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_plan`
--

CREATE TABLE `meal_plan` (
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `week_start` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plan`
--

INSERT INTO `meal_plan` (`plan_id`, `user_id`, `week_start`, `created_at`) VALUES
(1, 1, '2025-05-05', '2025-05-05 03:57:30'),
(2, 1, '2025-05-05', '2025-05-05 07:13:12'),
(3, 1, '2025-05-05', '2025-05-05 07:13:26'),
(4, 1, '2025-05-05', '2025-05-05 07:13:38');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plan_calendar`
--

CREATE TABLE `meal_plan_calendar` (
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_time` enum('Breakfast','Lunch','Dinner') NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_plan_entry`
--

CREATE TABLE `meal_plan_entry` (
  `entry_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_time` enum('Breakfast','Lunch','Dinner','Snack') NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `custom_title` varchar(255) DEFAULT NULL,
  `calories` decimal(6,2) NOT NULL,
  `status` enum('Planned','Completed','Skipped','Substituted') DEFAULT 'Planned',
  `notes` text DEFAULT NULL,
  `consumed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plan_entry`
--

INSERT INTO `meal_plan_entry` (`entry_id`, `plan_id`, `meal_date`, `meal_time`, `recipe_id`, `custom_title`, `calories`, `status`, `notes`, `consumed_at`) VALUES
(1, 1, '2025-05-05', 'Breakfast', 58, '', 30.00, 'Substituted', 'eggs', NULL),
(2, 1, '2025-05-06', 'Breakfast', 104, '', 120.00, 'Planned', NULL, NULL),
(3, 1, '2025-05-07', 'Breakfast', 111, '', 100.00, 'Planned', NULL, NULL),
(4, 1, '2025-05-05', 'Lunch', 67, '', 120.00, 'Planned', NULL, NULL),
(5, 1, '2025-05-05', 'Lunch', 67, '', 120.00, 'Planned', NULL, NULL),
(6, 1, '2025-05-06', 'Lunch', 67, '', 130.00, 'Planned', NULL, NULL),
(7, 1, '2025-05-07', 'Lunch', 79, '', 200.00, 'Planned', NULL, NULL),
(8, 1, '2025-05-08', 'Breakfast', 105, '', 80.00, 'Planned', NULL, NULL),
(9, 1, '2025-05-08', 'Lunch', 59, '', 60.00, 'Planned', NULL, NULL),
(10, 1, '2025-05-05', 'Dinner', 56, '', 100.00, '', NULL, '2025-05-05 06:59:42'),
(11, 2, '2025-05-05', 'Breakfast', 107, NULL, 480.00, 'Planned', NULL, NULL),
(12, 2, '2025-05-05', 'Lunch', 87, NULL, 320.00, 'Planned', NULL, NULL),
(13, 2, '2025-05-05', 'Dinner', 113, NULL, 450.00, 'Planned', NULL, NULL),
(14, 2, '2025-05-06', 'Breakfast', 57, NULL, 320.00, 'Planned', NULL, NULL),
(15, 2, '2025-05-06', 'Lunch', 69, NULL, 570.00, 'Planned', NULL, NULL),
(16, 2, '2025-05-06', 'Dinner', 78, NULL, 320.00, 'Planned', NULL, NULL),
(17, 2, '2025-05-07', 'Breakfast', 110, NULL, 530.00, 'Planned', NULL, NULL),
(18, 2, '2025-05-07', 'Lunch', 115, NULL, 480.00, 'Planned', NULL, NULL),
(19, 2, '2025-05-07', 'Dinner', 97, NULL, 470.00, 'Planned', NULL, NULL),
(20, 2, '2025-05-08', 'Breakfast', 74, NULL, 450.00, 'Planned', NULL, NULL),
(21, 2, '2025-05-08', 'Lunch', 88, NULL, 290.00, 'Planned', NULL, NULL),
(22, 2, '2025-05-08', 'Dinner', 90, NULL, 280.00, 'Planned', NULL, NULL),
(23, 2, '2025-05-09', 'Breakfast', 112, NULL, 490.00, 'Planned', NULL, NULL),
(24, 2, '2025-05-09', 'Lunch', 63, NULL, 350.00, 'Planned', NULL, NULL),
(25, 2, '2025-05-09', 'Dinner', 107, NULL, 480.00, 'Planned', NULL, NULL),
(26, 2, '2025-05-10', 'Breakfast', 110, NULL, 530.00, 'Planned', NULL, NULL),
(27, 2, '2025-05-10', 'Lunch', 114, NULL, 520.00, 'Planned', NULL, NULL),
(28, 2, '2025-05-10', 'Dinner', 108, NULL, 550.00, 'Planned', NULL, NULL),
(29, 2, '2025-05-11', 'Breakfast', 72, NULL, 410.00, 'Planned', NULL, NULL),
(30, 2, '2025-05-11', 'Lunch', 64, NULL, 290.00, 'Planned', NULL, NULL),
(31, 2, '2025-05-11', 'Dinner', 96, NULL, 420.00, 'Planned', NULL, NULL),
(32, 3, '2025-05-05', 'Breakfast', 81, NULL, 410.00, 'Planned', NULL, NULL),
(33, 3, '2025-05-05', 'Lunch', 98, NULL, 500.00, 'Planned', NULL, NULL),
(34, 3, '2025-05-05', 'Dinner', 93, NULL, 310.00, 'Planned', NULL, NULL),
(35, 3, '2025-05-06', 'Breakfast', 103, NULL, 410.00, 'Planned', NULL, NULL),
(36, 3, '2025-05-06', 'Lunch', 82, NULL, 680.00, 'Planned', NULL, NULL),
(37, 3, '2025-05-06', 'Dinner', 95, NULL, 330.00, 'Planned', NULL, NULL),
(38, 3, '2025-05-07', 'Breakfast', 99, NULL, 450.00, 'Planned', NULL, NULL),
(39, 3, '2025-05-07', 'Lunch', 71, NULL, 700.00, 'Planned', NULL, NULL),
(40, 3, '2025-05-07', 'Dinner', 101, NULL, 480.00, 'Planned', NULL, NULL),
(41, 3, '2025-05-08', 'Breakfast', 65, NULL, 310.00, 'Planned', NULL, NULL),
(42, 3, '2025-05-08', 'Lunch', 58, NULL, 400.00, 'Planned', NULL, NULL),
(43, 3, '2025-05-08', 'Dinner', 84, NULL, 380.00, 'Planned', NULL, NULL),
(44, 3, '2025-05-09', 'Breakfast', 60, NULL, 300.00, 'Planned', NULL, NULL),
(45, 3, '2025-05-09', 'Lunch', 106, NULL, 600.00, 'Planned', NULL, NULL),
(46, 3, '2025-05-09', 'Dinner', 95, NULL, 330.00, 'Planned', NULL, NULL),
(47, 3, '2025-05-10', 'Breakfast', 68, NULL, 480.00, 'Planned', NULL, NULL),
(48, 3, '2025-05-10', 'Lunch', 57, NULL, 320.00, 'Planned', NULL, NULL),
(49, 3, '2025-05-10', 'Dinner', 77, NULL, 750.00, 'Planned', NULL, NULL),
(50, 3, '2025-05-11', 'Breakfast', 111, NULL, 510.00, 'Planned', NULL, NULL),
(51, 3, '2025-05-11', 'Lunch', 90, NULL, 280.00, 'Planned', NULL, NULL),
(52, 3, '2025-05-11', 'Dinner', 73, NULL, 480.00, 'Planned', NULL, NULL),
(53, 4, '2025-05-05', 'Breakfast', 94, NULL, 360.00, 'Planned', NULL, NULL),
(54, 4, '2025-05-05', 'Lunch', 83, NULL, 430.00, 'Planned', NULL, NULL),
(55, 4, '2025-05-05', 'Dinner', 62, NULL, 320.00, 'Planned', NULL, NULL),
(56, 4, '2025-05-06', 'Breakfast', 91, NULL, 300.00, 'Planned', NULL, NULL),
(57, 4, '2025-05-06', 'Lunch', 61, NULL, 270.00, 'Planned', NULL, NULL),
(58, 4, '2025-05-06', 'Dinner', 102, NULL, 430.00, 'Planned', NULL, NULL),
(59, 4, '2025-05-07', 'Breakfast', 76, NULL, 450.00, 'Planned', NULL, NULL),
(60, 4, '2025-05-07', 'Lunch', 70, NULL, 460.00, 'Planned', NULL, NULL),
(61, 4, '2025-05-07', 'Dinner', 75, NULL, 510.00, 'Planned', NULL, NULL),
(62, 4, '2025-05-08', 'Breakfast', 93, NULL, 310.00, 'Planned', NULL, NULL),
(63, 4, '2025-05-08', 'Lunch', 109, NULL, 470.00, 'Planned', NULL, NULL),
(64, 4, '2025-05-08', 'Dinner', 98, NULL, 500.00, 'Planned', NULL, NULL),
(65, 4, '2025-05-09', 'Breakfast', 61, NULL, 270.00, 'Planned', NULL, NULL),
(66, 4, '2025-05-09', 'Lunch', 62, NULL, 320.00, 'Planned', NULL, NULL),
(67, 4, '2025-05-09', 'Dinner', 113, NULL, 450.00, 'Planned', NULL, NULL),
(68, 4, '2025-05-10', 'Breakfast', 115, NULL, 480.00, 'Planned', NULL, NULL),
(69, 4, '2025-05-10', 'Lunch', 81, NULL, 410.00, 'Planned', NULL, NULL),
(70, 4, '2025-05-10', 'Dinner', 118, NULL, 560.00, 'Planned', NULL, NULL),
(71, 4, '2025-05-11', 'Breakfast', 99, NULL, 450.00, 'Planned', NULL, NULL),
(72, 4, '2025-05-11', 'Lunch', 79, NULL, 500.00, 'Planned', NULL, NULL),
(73, 4, '2025-05-11', 'Dinner', 76, NULL, 450.00, 'Planned', NULL, NULL),
(74, 1, '2025-05-09', 'Breakfast', 80, '', 10.00, 'Planned', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pantry`
--

CREATE TABLE `pantry` (
  `pantry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `expiry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pantry`
--

INSERT INTO `pantry` (`pantry_id`, `user_id`, `ingredient_id`, `quantity`, `expiry_date`) VALUES
(71, 1, 630, 2.00, '2025-06-13'),
(75, 1, 667, 8.00, '2025-08-01'),
(77, 1, 602, 15.00, '2026-12-30'),
(78, 1, 603, 50.00, '2026-12-12'),
(79, 1, 601, 150.00, '2025-05-31'),
(80, 1, 611, 1.00, '2026-10-10'),
(83, 1, 616, 1.00, '2027-10-10');

-- --------------------------------------------------------

--
-- Table structure for table `recipeingredients`
--

CREATE TABLE `recipeingredients` (
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipeingredients`
--

INSERT INTO `recipeingredients` (`recipe_id`, `ingredient_id`, `quantity`) VALUES
(56, 601, 100.00),
(56, 602, 5.00),
(56, 603, 50.00),
(57, 602, 5.00),
(57, 604, 100.00),
(57, 605, 50.00),
(58, 607, 100.00),
(58, 608, 80.00),
(58, 609, 10.00),
(59, 605, 50.00),
(59, 610, 100.00),
(59, 611, 5.00),
(60, 612, 100.00),
(60, 613, 50.00),
(60, 614, 5.00),
(61, 612, 80.00),
(61, 615, 100.00),
(61, 616, 10.00),
(62, 615, 80.00),
(62, 617, 100.00),
(62, 618, 10.00),
(63, 619, 100.00),
(63, 620, 30.00),
(63, 621, 5.00),
(64, 609, 10.00),
(64, 615, 50.00),
(64, 622, 80.00),
(65, 607, 100.00),
(65, 613, 30.00),
(65, 623, 20.00),
(66, 603, 50.00),
(66, 609, 10.00),
(66, 611, 5.00),
(66, 616, 10.00),
(66, 624, 100.00),
(66, 625, 50.00),
(66, 626, 25.00),
(67, 603, 50.00),
(67, 609, 10.00),
(67, 611, 5.00),
(67, 616, 10.00),
(67, 625, 50.00),
(67, 627, 150.00),
(67, 628, 10.00),
(67, 629, 10.00),
(68, 609, 10.00),
(68, 611, 5.00),
(68, 615, 100.00),
(68, 616, 10.00),
(68, 630, 100.00),
(68, 631, 1.00),
(69, 603, 40.00),
(69, 609, 10.00),
(69, 611, 5.00),
(69, 616, 10.00),
(69, 625, 40.00),
(69, 632, 120.00),
(69, 633, 100.00),
(69, 634, 5.00),
(70, 603, 50.00),
(70, 609, 10.00),
(70, 611, 5.00),
(70, 625, 50.00),
(70, 634, 5.00),
(70, 635, 100.00),
(71, 609, 10.00),
(71, 611, 5.00),
(71, 613, 30.00),
(71, 618, 20.00),
(71, 625, 50.00),
(71, 626, 20.00),
(71, 627, 150.00),
(72, 602, 5.00),
(72, 603, 40.00),
(72, 609, 10.00),
(72, 611, 5.00),
(72, 612, 100.00),
(72, 613, 60.00),
(72, 616, 10.00),
(73, 603, 50.00),
(73, 609, 10.00),
(73, 611, 5.00),
(73, 616, 10.00),
(73, 625, 50.00),
(73, 628, 10.00),
(73, 636, 2.00),
(74, 603, 50.00),
(74, 609, 10.00),
(74, 611, 5.00),
(74, 616, 10.00),
(74, 625, 50.00),
(74, 637, 100.00),
(74, 639, 20.00),
(75, 603, 40.00),
(75, 609, 10.00),
(75, 611, 5.00),
(75, 616, 10.00),
(75, 624, 80.00),
(75, 625, 40.00),
(75, 638, 80.00),
(76, 603, 40.00),
(76, 609, 10.00),
(76, 611, 5.00),
(76, 616, 10.00),
(76, 624, 100.00),
(76, 625, 40.00),
(76, 640, 100.00),
(77, 603, 50.00),
(77, 609, 10.00),
(77, 611, 5.00),
(77, 613, 30.00),
(77, 641, 150.00),
(78, 603, 40.00),
(78, 609, 10.00),
(78, 625, 40.00),
(78, 634, 5.00),
(78, 642, 100.00),
(79, 609, 10.00),
(79, 611, 5.00),
(79, 616, 10.00),
(79, 632, 120.00),
(79, 643, 50.00),
(80, 602, 5.00),
(80, 611, 5.00),
(80, 621, 5.00),
(80, 622, 100.00),
(80, 628, 10.00),
(80, 644, 5.00),
(81, 603, 40.00),
(81, 606, 10.00),
(81, 609, 10.00),
(81, 611, 5.00),
(81, 624, 80.00),
(81, 625, 40.00),
(82, 609, 10.00),
(82, 611, 5.00),
(82, 616, 10.00),
(82, 625, 50.00),
(82, 627, 150.00),
(83, 609, 10.00),
(83, 611, 5.00),
(83, 615, 100.00),
(83, 645, 50.00),
(84, 603, 40.00),
(84, 606, 10.00),
(84, 609, 10.00),
(84, 611, 5.00),
(84, 625, 40.00),
(84, 636, 2.00),
(85, 603, 40.00),
(85, 609, 10.00),
(85, 611, 5.00),
(85, 625, 40.00),
(85, 646, 100.00),
(86, 603, 40.00),
(86, 609, 10.00),
(86, 625, 60.00),
(86, 636, 2.00),
(86, 667, 50.00),
(87, 636, 2.00),
(87, 640, 50.00),
(87, 670, 30.00),
(88, 617, 50.00),
(88, 625, 40.00),
(88, 665, 50.00),
(88, 678, 10.00),
(89, 671, 100.00),
(89, 673, 20.00),
(89, 677, 20.00),
(90, 675, 100.00),
(90, 678, 10.00),
(90, 680, 60.00),
(90, 681, 5.00),
(91, 617, 50.00),
(91, 637, 80.00),
(91, 672, 5.00),
(91, 676, 10.00),
(91, 678, 10.00),
(92, 636, 2.00),
(92, 670, 25.00),
(92, 672, 5.00),
(93, 617, 60.00),
(93, 669, 30.00),
(93, 674, 30.00),
(93, 679, 15.00),
(94, 625, 50.00),
(94, 636, 2.00),
(94, 666, 5.00),
(95, 668, 50.00),
(95, 674, 30.00),
(95, 676, 10.00),
(95, 679, 20.00),
(95, 680, 60.00),
(118, 610, 2.00),
(118, 705, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `servings` int(11) NOT NULL,
  `instructions` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `diet_preference` enum('Veg','Non-Veg','Vegan','Only Fish') DEFAULT NULL,
  `nationality` varchar(100) DEFAULT 'Indian',
  `difficulty_level` enum('Beginner','Intermediate','Pro') DEFAULT 'Intermediate',
  `total_calories` decimal(6,2) DEFAULT NULL,
  `meal_type` enum('Breakfast','Lunch','Dinner') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `user_id`, `title`, `servings`, `instructions`, `created_at`, `diet_preference`, `nationality`, `difficulty_level`, `total_calories`, `meal_type`) VALUES
(56, 1, 'Poha', 2, '1. Rinse poha and drain water. 2. Heat oil, add mustard seeds, curry leaves, green chilies. 3. Add onions and sauté. 4. Add turmeric, salt, and poha. Mix well and cook for 2 minutes.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Beginner', 250.00, 'Breakfast'),
(57, 1, 'Upma', 2, '1. Roast rava and keep aside. 2. Heat oil, add mustard seeds, urad dal, green chilies, and curry leaves. 3. Add onions and sauté. 4. Add water and salt. When boiling, add rava slowly and stir continuously.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Intermediate', 320.00, 'Breakfast'),
(58, 1, 'Aloo Paratha', 2, '1. Prepare dough with wheat flour. 2. Make filling with mashed potatoes, spices, and herbs. 3. Stuff filling into dough and roll out parathas. 4. Cook on tawa with ghee.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Beginner', 400.00, 'Breakfast'),
(59, 1, 'Idli', 3, '1. Soak rice and urad dal. 2. Grind separately and ferment overnight. 3. Pour batter into idli molds and steam for 10-12 minutes.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Beginner', 280.00, 'Breakfast'),
(60, 1, 'Dhokla', 3, '1. Prepare batter with besan, yogurt, and spices. 2. Add ENO and steam for 15 minutes. 3. Temper with mustard seeds, curry leaves, and green chilies.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Beginner', 300.00, 'Breakfast'),
(61, 1, 'Vegetable Cheela', 2, '1. Mix besan, chopped veggies, and spices. 2. Add water to make batter. 3. Pour on hot tawa and cook on both sides.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Beginner', 270.00, 'Breakfast'),
(62, 1, 'Vegetable Sandwich', 2, '1. Mix boiled veggies with salt and pepper. 2. Place between bread slices and grill or toast. 3. Serve with chutney or ketchup.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Intermediate', 320.00, 'Breakfast'),
(63, 1, 'Sabudana Khichdi', 2, '1. Soak sabudana overnight. 2. Heat ghee, add cumin, chilies, peanuts. 3. Add soaked sabudana, salt, and cook for 5-7 minutes.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Beginner', 350.00, 'Breakfast'),
(64, 1, 'Moong Dal Chilla', 2, '1. Soak moong dal and grind to a paste. 2. Add spices and chopped vegetables. 3. Pour batter on tawa and cook till golden.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Intermediate', 290.00, 'Breakfast'),
(65, 1, 'Thepla', 2, '1. Knead dough with wheat flour, methi, yogurt, and spices. 2. Roll into flatbreads and cook on tawa. 3. Serve with pickle or curd.', '2025-05-04 07:39:17', 'Veg', 'Indian', 'Beginner', 310.00, 'Breakfast'),
(66, 1, 'Paneer Butter Masala', 3, '1. Heat butter and sauté onions and tomatoes. 2. Add spices and blend into a smooth gravy. 3. Add paneer cubes and simmer until soft. 4. Garnish with cream.', '2025-05-04 07:48:10', 'Veg', 'Indian', 'Beginner', 520.00, 'Lunch'),
(67, 1, 'Chicken Curry', 3, '1. Marinate chicken with spices. 2. Sauté onions, garlic, ginger. 3. Add tomatoes and cook to a gravy. 4. Add chicken and simmer until cooked.', '2025-05-04 07:48:10', 'Non-Veg', 'Indian', 'Intermediate', 650.00, 'Lunch'),
(68, 1, 'Vegetable Biryani', 4, '1. Sauté vegetables with spices. 2. Layer with parboiled basmati rice. 3. Add saffron milk and cook on dum. 4. Serve with raita.', '2025-05-04 07:48:10', 'Veg', 'Indian', 'Intermediate', 480.00, 'Lunch'),
(69, 1, 'Fish Curry', 2, '1. Marinate fish with turmeric and salt. 2. Fry onions, add spices and tomatoes. 3. Add coconut milk and simmer fish. 4. Garnish with coriander.', '2025-05-04 07:48:10', 'Only Fish', 'Indian', 'Beginner', 570.00, 'Lunch'),
(70, 1, 'Rajma Masala', 3, '1. Soak and pressure cook rajma. 2. Fry onions, tomatoes, and spices. 3. Add rajma and simmer. 4. Garnish with coriander.', '2025-05-04 07:48:10', 'Veg', 'Indian', 'Intermediate', 460.00, 'Lunch'),
(71, 1, 'Butter Chicken', 3, '1. Marinate chicken in yogurt and spices. 2. Grill and then simmer in tomato-butter gravy. 3. Add cream before serving.', '2025-05-04 07:48:10', 'Non-Veg', 'Indian', 'Intermediate', 700.00, 'Lunch'),
(72, 1, 'Kadhi Pakora', 3, '1. Make pakoras with besan. 2. Prepare yogurt and besan curry with spices. 3. Add pakoras and simmer. 4. Temper with mustard seeds.', '2025-05-04 07:48:10', 'Veg', 'Indian', 'Beginner', 410.00, 'Lunch'),
(73, 1, 'Egg Curry', 3, '1. Boil eggs and slit slightly. 2. Prepare masala gravy with onions and tomatoes. 3. Add eggs and simmer for 10 minutes.', '2025-05-04 07:48:10', 'Non-Veg', 'Indian', 'Intermediate', 480.00, 'Lunch'),
(74, 1, 'Chole', 3, '1. Soak and cook chickpeas. 2. Fry onions, tomatoes, ginger garlic paste. 3. Add spices and simmer with chickpeas. 4. Garnish with cilantro.', '2025-05-04 07:48:10', 'Veg', 'Indian', 'Intermediate', 450.00, 'Lunch'),
(75, 1, 'Matar Paneer', 3, '1. Fry paneer cubes. 2. Sauté onions and tomatoes with spices. 3. Add peas and paneer. Simmer till soft.', '2025-05-04 07:48:10', 'Veg', 'Indian', 'Beginner', 510.00, 'Lunch'),
(76, 1, 'Palak Paneer', 3, '1. Blanch spinach and blend. 2. Sauté onions and tomatoes with spices. 3. Add paneer and spinach puree. 4. Simmer and serve hot.', '2025-05-04 07:53:11', 'Veg', 'Indian', 'Beginner', 450.00, 'Dinner'),
(77, 1, 'Mutton Rogan Josh', 3, '1. Marinate mutton with yogurt and spices. 2. Fry onions, add meat and cook. 3. Add water and simmer until tender.', '2025-05-04 07:53:11', 'Non-Veg', 'Indian', 'Intermediate', 750.00, 'Dinner'),
(78, 1, 'Baingan Bharta', 2, '1. Roast brinjal on open flame. 2. Mash and cook with onions, tomatoes, and spices. 3. Garnish with coriander.', '2025-05-04 07:53:11', 'Veg', 'Indian', 'Beginner', 320.00, 'Dinner'),
(79, 1, 'Fish Fry', 2, '1. Marinate fish with spices. 2. Coat with semolina and shallow fry. 3. Serve with lemon wedges and onions.', '2025-05-04 07:53:11', 'Only Fish', 'Indian', 'Intermediate', 500.00, 'Dinner'),
(80, 1, 'Dal Tadka', 3, '1. Pressure cook dal. 2. Temper with mustard, cumin, garlic, and red chilies. 3. Add to dal and simmer.', '2025-05-04 07:53:11', 'Veg', 'Indian', 'Intermediate', 390.00, 'Dinner'),
(81, 1, 'Paneer Bhurji', 2, '1. Crumble paneer. 2. Sauté onions, tomatoes, green chilies, and spices. 3. Add paneer and mix well.', '2025-05-04 07:53:11', 'Veg', 'Indian', 'Beginner', 410.00, 'Dinner'),
(82, 1, 'Chicken Tikka Masala', 3, '1. Marinate chicken and grill. 2. Prepare masala gravy. 3. Add chicken and simmer in gravy.', '2025-05-04 07:53:11', 'Non-Veg', 'Indian', 'Intermediate', 680.00, 'Dinner'),
(83, 1, 'Vegetable Kurma', 3, '1. Cook mixed vegetables. 2. Add ground coconut paste and spices. 3. Simmer till thick and creamy.', '2025-05-04 07:53:11', 'Veg', 'Indian', 'Intermediate', 430.00, 'Dinner'),
(84, 1, 'Egg Bhurji', 2, '1. Sauté onions, tomatoes, and chilies. 2. Add beaten eggs and scramble. 3. Cook till fluffy.', '2025-05-04 07:53:11', 'Non-Veg', 'Indian', 'Beginner', 380.00, 'Dinner'),
(85, 1, 'Tofu Masala', 2, '1. Sauté onions and tomatoes. 2. Add tofu cubes and cook with spices. 3. Simmer and serve hot.', '2025-05-04 07:53:11', 'Vegan', 'Indian', 'Intermediate', 400.00, 'Dinner'),
(86, 1, 'Shakshuka', 2, '1. Sauté onions and bell peppers. 2. Add tomatoes and spices. 3. Crack eggs into sauce and cook until set.', '2025-05-04 08:00:38', 'Non-Veg', 'Mediterranean', 'Beginner', 350.00, 'Breakfast'),
(87, 1, 'Feta and Spinach Omelette', 2, '1. Beat eggs, add spinach and crumbled feta. 2. Cook in pan until firm and golden.', '2025-05-04 08:00:38', 'Non-Veg', 'Mediterranean', 'Beginner', 320.00, 'Breakfast'),
(88, 1, 'Avocado Toast with Tomato', 1, '1. Toast bread. 2. Spread mashed avocado. 3. Top with sliced tomatoes and olive oil.', '2025-05-04 08:00:38', 'Vegan', 'Mediterranean', 'Beginner', 290.00, 'Breakfast'),
(89, 1, 'Greek Yogurt with Honey and Nuts', 1, '1. Add honey and chopped nuts to Greek yogurt. 2. Top with fruit if desired.', '2025-05-04 08:00:38', '', 'Mediterranean', 'Intermediate', 330.00, 'Breakfast'),
(90, 1, 'Labneh with Olive Oil and Za\'atar', 2, '1. Spread labneh in bowl. 2. Drizzle with olive oil and sprinkle za\'atar. 3. Serve with pita bread.', '2025-05-04 08:00:38', '', 'Mediterranean', 'Beginner', 280.00, 'Breakfast'),
(91, 1, 'Mediterranean Chickpea Toast', 1, '1. Mash chickpeas with olive oil and lemon. 2. Spread on toast. 3. Top with herbs.', '2025-05-04 08:00:38', 'Vegan', 'Mediterranean', 'Intermediate', 300.00, 'Breakfast'),
(92, 1, 'Herbed Scrambled Eggs with Feta', 2, '1. Beat eggs with herbs. 2. Cook and add crumbled feta just before done.', '2025-05-04 08:00:38', 'Non-Veg', 'Mediterranean', 'Intermediate', 340.00, 'Breakfast'),
(93, 1, 'Olive and Cucumber Sandwich', 1, '1. Spread hummus on bread. 2. Add sliced olives and cucumbers. 3. Close sandwich and serve.', '2025-05-04 08:00:38', 'Vegan', 'Mediterranean', 'Intermediate', 310.00, 'Breakfast'),
(94, 1, 'Tomato Basil Frittata', 2, '1. Whisk eggs and pour into pan with tomatoes and basil. 2. Cook until set and slightly golden.', '2025-05-04 08:00:38', 'Non-Veg', 'Mediterranean', 'Intermediate', 360.00, 'Breakfast'),
(95, 1, 'Mediterranean Pita Pockets', 2, '1. Fill pita with chopped vegetables, hummus, and olives. 2. Drizzle with lemon juice.', '2025-05-04 08:00:38', 'Vegan', 'Mediterranean', 'Beginner', 330.00, 'Breakfast'),
(96, 1, 'Mediterranean Quinoa Salad', 2, 'Step-by-step preparation method for Mediterranean Quinoa Salad.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 420.00, 'Lunch'),
(97, 1, 'Falafel Wrap', 2, 'Step-by-step preparation method for Falafel Wrap.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Beginner', 470.00, 'Lunch'),
(98, 1, 'Greek Chicken Bowl', 2, 'Step-by-step preparation method for Greek Chicken Bowl.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 500.00, 'Lunch'),
(99, 1, 'Chickpea Shawarma Salad', 2, 'Step-by-step preparation method for Chickpea Shawarma Salad.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Beginner', 450.00, 'Lunch'),
(100, 1, 'Hummus Veggie Wrap', 2, 'Step-by-step preparation method for Hummus Veggie Wrap.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Intermediate', 400.00, 'Lunch'),
(101, 1, 'Orzo Pasta Salad', 2, 'Step-by-step preparation method for Orzo Pasta Salad.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Beginner', 480.00, 'Lunch'),
(102, 1, 'Grilled Eggplant Sandwich', 2, 'Step-by-step preparation method for Grilled Eggplant Sandwich.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 430.00, 'Lunch'),
(103, 1, 'Lentil Tabbouleh', 2, 'Step-by-step preparation method for Lentil Tabbouleh.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Beginner', 410.00, 'Lunch'),
(104, 1, 'Stuffed Grape Leaves', 2, 'Step-by-step preparation method for Stuffed Grape Leaves.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 390.00, 'Lunch'),
(105, 1, 'Tuna Niçoise Salad', 2, 'Step-by-step preparation method for Tuna Niçoise Salad.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Beginner', 460.00, 'Lunch'),
(106, 1, 'Grilled Lamb Chops', 2, 'Step-by-step preparation method for Grilled Lamb Chops.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 600.00, 'Dinner'),
(107, 1, 'Stuffed Bell Peppers', 2, 'Step-by-step preparation method for Stuffed Bell Peppers.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Beginner', 480.00, 'Dinner'),
(108, 1, 'Seafood Paella', 2, 'Step-by-step preparation method for Seafood Paella.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 550.00, 'Dinner'),
(109, 1, 'Vegetable Moussaka', 2, 'Step-by-step preparation method for Vegetable Moussaka.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Beginner', 470.00, 'Dinner'),
(110, 1, 'Lemon Herb Chicken', 2, 'Step-by-step preparation method for Lemon Herb Chicken.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 530.00, 'Dinner'),
(111, 1, 'Baked Feta Pasta', 2, 'Step-by-step preparation method for Baked Feta Pasta.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Beginner', 510.00, 'Dinner'),
(112, 1, 'Spinach and Cheese Cannelloni', 2, 'Step-by-step preparation method for Spinach and Cheese Cannelloni.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Intermediate', 490.00, 'Dinner'),
(113, 1, 'Roasted Veggie Couscous', 2, 'Step-by-step preparation method for Roasted Veggie Couscous.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Beginner', 450.00, 'Dinner'),
(114, 1, 'Shrimp Saganaki', 2, 'Step-by-step preparation method for Shrimp Saganaki.', '2025-05-04 08:05:24', 'Non-Veg', 'Mediterranean', 'Intermediate', 520.00, 'Dinner'),
(115, 1, 'Eggplant Parmesan', 2, 'Step-by-step preparation method for Eggplant Parmesan.', '2025-05-04 08:05:24', 'Veg', 'Mediterranean', 'Beginner', 480.00, 'Dinner'),
(117, 1, 'Test', 1, 'test', '2025-05-04 22:10:40', 'Veg', 'Indian', 'Beginner', 560.00, 'Lunch'),
(118, 1, 'Test', 4, 'test', '2025-05-04 22:33:52', 'Veg', 'Pak', 'Beginner', 560.00, 'Breakfast');

-- --------------------------------------------------------

--
-- Table structure for table `recipetags`
--

CREATE TABLE `recipetags` (
  `recipe_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shoppinglist`
--

CREATE TABLE `shoppinglist` (
  `list_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shoppinglist`
--

INSERT INTO `shoppinglist` (`list_id`, `user_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10),
(11, 11),
(12, 12),
(13, 13),
(14, 14),
(15, 15),
(16, 16),
(17, 17),
(18, 18),
(19, 19),
(20, 20),
(21, 21),
(22, 22),
(23, 23),
(24, 24),
(25, 25),
(26, 26),
(27, 27),
(28, 28),
(29, 29),
(30, 30);

-- --------------------------------------------------------

--
-- Table structure for table `shoppinglistdetails`
--

CREATE TABLE `shoppinglistdetails` (
  `list_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `measurement_unit` varchar(50) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shoppinglistdetails`
--

INSERT INTO `shoppinglistdetails` (`list_id`, `ingredient_id`, `quantity`, `measurement_unit`, `added_at`) VALUES
(1, 605, 50.00, 'g', '2025-05-05 04:55:04'),
(1, 610, 100.00, 'g', '2025-05-05 04:55:04'),
(1, 611, 9.00, 'g', '2025-05-05 04:55:04'),
(1, 621, 5.00, 'g', '2025-05-05 07:49:41'),
(1, 622, 100.00, 'g', '2025-05-05 07:49:41'),
(1, 628, 10.00, 'g', '2025-05-05 07:49:41'),
(1, 644, 5.00, 'g', '2025-05-05 07:49:41');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `auth_token` varchar(255) DEFAULT NULL,
  `auth_secret` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `created_at`, `auth_token`, `auth_secret`) VALUES
(1, 'Ananya Sharma', 'ananya.sharma@example.com', 'hash1', '2025-04-30 19:06:50', NULL, NULL),
(2, 'Rohan Mehta', 'rohan.mehta@example.com', 'hash2', '2025-04-30 19:06:50', NULL, NULL),
(3, 'Priya Nair', 'priya.nair@example.com', 'hash3', '2025-04-30 19:06:50', NULL, NULL),
(4, 'Arjun Reddy', 'arjun.reddy@example.com', 'hash4', '2025-04-30 19:06:50', NULL, NULL),
(5, 'Sneha Iyer', 'sneha.iyer@example.com', 'hash5', '2025-04-30 19:06:50', NULL, NULL),
(6, 'Rahul Khanna', 'rahul.khanna@example.com', 'hash6', '2025-04-30 19:06:50', NULL, NULL),
(7, 'Divya Patel', 'divya.patel@example.com', 'hash7', '2025-04-30 19:06:50', NULL, NULL),
(8, 'Karan Malhotra', 'karan.malhotra@example.com', 'hash8', '2025-04-30 19:06:50', NULL, NULL),
(9, 'Neha Verma', 'neha.verma@example.com', 'hash9', '2025-04-30 19:06:50', NULL, NULL),
(10, 'Amit Joshi', 'amit.joshi@example.com', 'hash10', '2025-04-30 19:06:50', NULL, NULL),
(11, 'Ishita Roy', 'ishita.roy@example.com', 'hash11', '2025-04-30 19:06:50', NULL, NULL),
(12, 'Manav Singh', 'manav.singh@example.com', 'hash12', '2025-04-30 19:06:50', NULL, NULL),
(13, 'Tanvi Desai', 'tanvi.desai@example.com', 'hash13', '2025-04-30 19:06:50', NULL, NULL),
(14, 'Siddharth Ghosh', 'siddharth.ghosh@example.com', 'hash14', '2025-04-30 19:06:50', NULL, NULL),
(15, 'Meera Kumar', 'meera.kumar@example.com', 'hash15', '2025-04-30 19:06:50', NULL, NULL),
(16, 'Kabir Chopra', 'kabir.chopra@example.com', 'hash16', '2025-04-30 19:06:50', NULL, NULL),
(17, 'Ritika Shukla', 'ritika.shukla@example.com', 'hash17', '2025-04-30 19:06:50', NULL, NULL),
(18, 'Aditya Rastogi', 'aditya.rastogi@example.com', 'hash18', '2025-04-30 19:06:50', NULL, NULL),
(19, 'Pooja Bajaj', 'pooja.bajaj@example.com', 'hash19', '2025-04-30 19:06:50', NULL, NULL),
(20, 'Nikhil Chaudhary', 'nikhil.chaudhary@example.com', 'hash20', '2025-04-30 19:06:50', NULL, NULL),
(21, 'Simran Agarwal', 'simran.agarwal@example.com', 'hash21', '2025-04-30 19:06:50', NULL, NULL),
(22, 'Raj Pandey', 'raj.pandey@example.com', 'hash22', '2025-04-30 19:06:50', NULL, NULL),
(23, 'Shreya Bansal', 'shreya.bansal@example.com', 'hash23', '2025-04-30 19:06:50', NULL, NULL),
(24, 'Varun Dubey', 'varun.dubey@example.com', 'hash24', '2025-04-30 19:06:50', NULL, NULL),
(25, 'Naina Mishra', 'naina.mishra@example.com', 'hash25', '2025-04-30 19:06:50', NULL, NULL),
(26, 'Kavya Rana', 'kavya.rana@example.com', 'hash26', '2025-04-30 19:06:50', NULL, NULL),
(27, 'Vikram Trivedi', 'vikram.trivedi@example.com', 'hash27', '2025-04-30 19:06:50', NULL, NULL),
(28, 'Alia Saxena', 'alia.saxena@example.com', 'hash28', '2025-04-30 19:06:50', NULL, NULL),
(29, 'Dev Thakur', 'dev.thakur@example.com', 'hash29', '2025-04-30 19:06:50', NULL, NULL),
(30, 'Pari Kapoor', 'pari.kapoor@example.com', 'hash30', '2025-04-30 19:06:50', NULL, NULL),
(31, 'Sai', 'saim@gmail.com', '$2y$10$uEF/OfwGrNvP2pWx/U4ppOZ/mVWd80P/R1xmPJKnmZguf0eJ6Na2e', '2025-04-30 19:26:17', NULL, NULL),
(32, 'Sai1', 'saim1@gmail.com', '$2y$10$fORIOvtmwRcE9HVCPmtKwuHLysGrgYQiieu6aqomOzuQy0dupY3Qe', '2025-04-30 19:27:18', NULL, NULL),
(33, 'saim', 'saim2@gmail.com', '$2y$10$STYmpRqY2QmfwDfJFZNeHuAHR7JKc7dDWJdYVnjfuhiYmzyyOxLTe', '2025-04-30 21:25:54', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_meal_entries`
--

CREATE TABLE `user_meal_entries` (
  `entry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_time` enum('Breakfast','Lunch','Dinner','Snack') NOT NULL,
  `calories_consumed` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL,
  `meal_plan_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_meal_entries`
--

INSERT INTO `user_meal_entries` (`entry_id`, `user_id`, `meal_date`, `meal_time`, `calories_consumed`, `description`, `meal_plan_id`) VALUES
(1, 1, '2001-09-12', 'Breakfast', 250.00, NULL, NULL),
(2, 1, '2001-09-12', 'Breakfast', 250.00, NULL, NULL),
(3, 2, '2001-09-12', 'Breakfast', 250.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_meal_log`
--

CREATE TABLE `user_meal_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_time` enum('Breakfast','Lunch','Dinner','Snack') NOT NULL,
  `calories` decimal(6,2) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consumption`
--
ALTER TABLE `consumption`
  ADD PRIMARY KEY (`consumption_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `mealplan`
--
ALTER TABLE `mealplan`
  ADD PRIMARY KEY (`meal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mealplanrecipes`
--
ALTER TABLE `mealplanrecipes`
  ADD PRIMARY KEY (`meal_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `meal_plan`
--
ALTER TABLE `meal_plan`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meal_plan_calendar`
--
ALTER TABLE `meal_plan_calendar`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `meal_plan_entry`
--
ALTER TABLE `meal_plan_entry`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `pantry`
--
ALTER TABLE `pantry`
  ADD PRIMARY KEY (`pantry_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `recipeingredients`
--
ALTER TABLE `recipeingredients`
  ADD PRIMARY KEY (`recipe_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipetags`
--
ALTER TABLE `recipetags`
  ADD PRIMARY KEY (`recipe_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `shoppinglist`
--
ALTER TABLE `shoppinglist`
  ADD PRIMARY KEY (`list_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shoppinglistdetails`
--
ALTER TABLE `shoppinglistdetails`
  ADD PRIMARY KEY (`list_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_meal_entries`
--
ALTER TABLE `user_meal_entries`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_meal_plan_id` (`meal_plan_id`);

--
-- Indexes for table `user_meal_log`
--
ALTER TABLE `user_meal_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consumption`
--
ALTER TABLE `consumption`
  MODIFY `consumption_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=706;

--
-- AUTO_INCREMENT for table `mealplan`
--
ALTER TABLE `mealplan`
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `meal_plan`
--
ALTER TABLE `meal_plan`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `meal_plan_calendar`
--
ALTER TABLE `meal_plan_calendar`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_plan_entry`
--
ALTER TABLE `meal_plan_entry`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `pantry`
--
ALTER TABLE `pantry`
  MODIFY `pantry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `shoppinglist`
--
ALTER TABLE `shoppinglist`
  MODIFY `list_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `user_meal_entries`
--
ALTER TABLE `user_meal_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_meal_log`
--
ALTER TABLE `user_meal_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consumption`
--
ALTER TABLE `consumption`
  ADD CONSTRAINT `consumption_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consumption_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `mealplan`
--
ALTER TABLE `mealplan`
  ADD CONSTRAINT `mealplan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `mealplanrecipes`
--
ALTER TABLE `mealplanrecipes`
  ADD CONSTRAINT `mealplanrecipes_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `mealplan` (`meal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mealplanrecipes_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plan`
--
ALTER TABLE `meal_plan`
  ADD CONSTRAINT `meal_plan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plan_calendar`
--
ALTER TABLE `meal_plan_calendar`
  ADD CONSTRAINT `meal_plan_calendar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plan_calendar_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plan_entry`
--
ALTER TABLE `meal_plan_entry`
  ADD CONSTRAINT `meal_plan_entry_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `meal_plan` (`plan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plan_entry_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE SET NULL;

--
-- Constraints for table `pantry`
--
ALTER TABLE `pantry`
  ADD CONSTRAINT `pantry_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pantry_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipeingredients`
--
ALTER TABLE `recipeingredients`
  ADD CONSTRAINT `recipeingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipeingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipetags`
--
ALTER TABLE `recipetags`
  ADD CONSTRAINT `recipetags_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipetags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `shoppinglist`
--
ALTER TABLE `shoppinglist`
  ADD CONSTRAINT `shoppinglist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `shoppinglistdetails`
--
ALTER TABLE `shoppinglistdetails`
  ADD CONSTRAINT `shoppinglistdetails_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `shoppinglist` (`list_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shoppinglistdetails_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_meal_entries`
--
ALTER TABLE `user_meal_entries`
  ADD CONSTRAINT `user_meal_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_meal_log`
--
ALTER TABLE `user_meal_log`
  ADD CONSTRAINT `user_meal_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
