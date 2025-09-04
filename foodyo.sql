-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: May 26, 2025 at 07:03 AM
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
-- Database: `foodyo`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `item_id`, `quantity`, `created_at`, `updated_at`) VALUES
(17, 5, 71, 1, '2025-05-14 05:47:34', '2025-05-14 05:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `created_at`) VALUES
(1, 'Pizza', 'images/pizza.jpg', '2025-05-05 09:46:43'),
(2, 'Burger', 'images/burger.jpg', '2025-05-05 09:46:43'),
(3, 'Pasta', 'images/pasta.jpg', '2025-05-05 09:46:43'),
(4, 'Sushi', 'images/sushi.jpg', '2025-05-05 09:46:43'),
(5, 'Indian', 'images/indian.jpg', '2025-05-05 09:46:43'),
(6, 'Desserts', 'images/desserts.jpg', '2025-05-05 09:46:43');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_partners`
--

CREATE TABLE `delivery_partners` (
  `partner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `vehicle_number` varchar(20) DEFAULT NULL,
  `status` enum('available','busy','offline') DEFAULT 'offline',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `created_at`) VALUES
(1, 1, 'Margherita Pizza', 'Fresh tomatoes, mozzarella, basil, and olive oil', 299.00, 'images/margherita-pizza.jpg', 1, '2025-05-05 09:46:43'),
(2, 1, 'Pepperoni Pizza', 'Classic pepperoni with extra cheese', 349.00, 'images/pepperoni-pizza.jpg', 1, '2025-05-05 09:46:43'),
(3, 2, 'Classic Burger', 'Juicy beef patty with lettuce and special sauce', 199.00, 'images/classic-burger.jpg', 1, '2025-05-05 09:46:43'),
(4, 2, 'Chicken Burger', 'Grilled chicken with fresh vegetables', 249.00, 'images/chicken-burger.jpg', 1, '2025-05-05 09:46:43'),
(5, 3, 'Pasta Carbonara', 'Creamy pasta with bacon and parmesan', 249.00, 'images/pasta-carbonara.jpg', 1, '2025-05-05 09:46:43'),
(6, 3, 'Pasta Alfredo', 'Fettuccine in rich cream sauce', 229.00, 'images/pasta-alfredo.jpg', 1, '2025-05-05 09:46:43'),
(7, 4, 'Sushi Platter', 'Assorted fresh sushi with soy sauce', 399.00, 'images/sushi-platter.jpg', 1, '2025-05-05 09:46:43'),
(8, 5, 'Butter Chicken', 'Tender chicken in rich tomato gravy', 349.00, 'images/butter-chicken.jpg', 1, '2025-05-05 09:46:43'),
(9, 5, 'Veg Biryani', 'Fragrant basmati rice with mixed vegetables', 299.00, 'images/veg-biryani.jpg', 1, '2025-05-05 09:46:43'),
(10, 6, 'Gulab Jamun', 'Sweet milk solids dumplings in sugar syrup', 149.00, 'images/gulab-jamun.jpg', 1, '2025-05-05 09:46:43'),
(11, 6, 'Ice Cream', 'Vanilla ice cream with chocolate sauce', 99.00, 'images/ice-cream.jpg', 1, '2025-05-05 09:46:43'),
(25, 1, 'BBQ Chicken', 'Grilled chicken with BBQ sauce', 269.00, 'images\\BBQ Chicken.jpg', 1, '2025-05-06 09:04:32'),
(26, 1, 'Veggie Delight', 'Loaded with fresh vegetables', 229.00, 'images\\Veggie Delight.jpg', 1, '2025-05-06 09:04:32'),
(27, 1, 'Paneer Tikka', 'Indian paneer cubes on pizza', 259.00, 'images\\Paneer Tikka.jpg', 1, '2025-05-06 09:04:32'),
(28, 1, 'Four Cheese', 'Blend of 4 premium cheeses', 279.00, 'images\\Four Cheese.jpg', 1, '2025-05-06 09:04:32'),
(31, 1, 'Mexican Green Wave', 'Spicy jalapeno and capsicum', 249.00, 'images\\Mexican Green Wave.jpg', 1, '2025-05-06 09:04:32'),
(32, 1, 'Cheese Burst', 'Extra cheese layered inside crust', 289.00, 'images\\Cheese Burger.jpg', 1, '2025-05-06 09:04:32'),
(33, 2, 'Classic Veg Burger', 'Aloo patty with lettuce and mayo', 99.00, 'images\\Classic Veg Burger.jpg', 1, '2025-05-06 09:04:32'),
(34, 2, 'Cheese Burger', 'Grilled patty with cheese', 129.00, 'images\\Cheese Burger.jpg', 1, '2025-05-06 09:04:32'),
(36, 2, 'Double Patty Burger', 'Two patties with extra toppings', 179.00, 'images\\Double Patty Burger.jpg', 1, '2025-05-06 09:04:32'),
(37, 2, 'Paneer Burger', 'Crispy paneer patty with mayo', 139.00, 'images\\Paneer Burger.jpg', 1, '2025-05-06 09:04:32'),
(38, 2, 'Spicy Veg Burger', 'Spicy patty with jalapenos', 119.00, 'images\\Spicy Veg Burger.jpg', 1, '2025-05-06 09:04:32'),
(39, 2, 'Bacon Burger', 'Smoky bacon and beef patty', 199.00, 'images\\Bacon Burger.jpg', 1, '2025-05-06 09:04:32'),
(40, 2, 'Grilled Chicken Burger', 'Low-fat grilled chicken option', 159.00, 'images\\Grilled Chicken Burge.jpg', 1, '2025-05-06 09:04:32'),
(41, 2, 'Mushroom Burger', 'Juicy mushrooms and cheese', 149.00, 'images\\Mushroom Burger.jpg', 1, '2025-05-06 09:04:32'),
(42, 2, 'Fish Burger', 'Crispy fish patty and tartar sauce', 169.00, 'images\\Fish Burger.jpg', 1, '2025-05-06 09:04:32'),
(43, 3, 'Penne Alfredo', 'Creamy white sauce pasta', 229.00, 'images\\Penne Alfredo.jpg', 1, '2025-05-06 09:04:32'),
(44, 3, 'Spaghetti Bolognese', 'Red sauce with ground meat', 249.00, 'images\\Spaghetti Bolognese.jpg', 1, '2025-05-06 09:04:32'),
(45, 3, 'Pasta Arrabiata', 'Spicy red sauce penne', 219.00, 'images\\Pasta Arrabiata.jpg', 1, '2025-05-06 09:04:32'),
(46, 3, 'Cheesy Macaroni', 'Macaroni with cheese sauce', 199.00, 'images\\Cheese Macaroni.jpg', 1, '2025-05-06 09:04:32'),
(47, 3, 'Pesto Pasta', 'Basil pesto with penne', 239.00, 'images\\Pesto Pasta.jpg', 1, '2025-05-06 09:04:32'),
(48, 3, 'Veggie Pasta', 'Loaded with fresh vegetables', 209.00, 'images\\Veggie Pasta.jpg', 1, '2025-05-06 09:04:32'),
(49, 3, 'Mushroom Pasta', 'Creamy mushroom sauce', 229.00, 'images\\Mushroom Pasta.jpg', 1, '2025-05-06 09:04:32'),
(50, 3, 'Chicken Alfredo', 'Chicken with white sauce', 259.00, 'images\\Chicken Alfredo.jpg', 1, '2025-05-06 09:04:32'),
(51, 3, 'Garlic Butter Pasta', 'Garlic flavored spaghetti', 189.00, 'images\\Garlic Butter Pasta.jpg', 1, '2025-05-06 09:04:32'),
(52, 3, 'Lasagna', 'Layered baked pasta with cheese', 269.00, 'images\\Lasagna.jpg', 1, '2025-05-06 09:04:32'),
(53, 4, 'California Roll', 'Crab, avocado and cucumber', 299.00, 'images\\California Roll.jpg', 1, '2025-05-06 09:04:32'),
(54, 4, 'Spicy Tuna Roll', 'Tuna with chili mayo', 329.00, 'images\\Spicy Tuna Roll.jpg', 1, '2025-05-06 09:04:32'),
(55, 4, 'Salmon Nigiri', 'Sliced salmon on rice', 349.00, 'images\\Salmon Nigiri.jpg', 1, '2025-05-06 09:04:32'),
(56, 4, 'Eel Roll', 'Grilled eel with sauce', 359.00, 'images\\Eel Roll.jpg', 1, '2025-05-06 09:04:32'),
(57, 4, 'Vegetable Roll', 'Carrots, cucumber, and avocado', 269.00, 'images\\Vegetable Roll.jpg', 1, '2025-05-06 09:04:32'),
(58, 4, 'Shrimp Tempura Roll', 'Fried shrimp with rice', 339.00, 'images\\Shrimp Tempura Roll.jpg', 1, '2025-05-06 09:04:32'),
(59, 4, 'Dragon Roll', 'Eel and cucumber with avocado', 379.00, 'images\\Dragon Roll.jpg', 1, '2025-05-06 09:04:32'),
(60, 4, 'Rainbow Roll', 'Mixed fish and avocado on top', 399.00, 'images\\Rainbow Roll.jpg', 1, '2025-05-06 09:04:32'),
(61, 4, 'Tamago Nigiri', 'Sweet egg on sushi rice', 289.00, 'images\\Tamago Nigiri.jpg', 1, '2025-05-06 09:04:32'),
(62, 4, 'Philadelphia Roll', 'Salmon, cream cheese, cucumber', 319.00, 'images\\Philadelphia Roll.jpg', 1, '2025-05-06 09:04:32'),
(63, 5, 'Butter Chicken', 'Creamy tomato chicken curry', 299.00, 'images\\Butter Chicken tomato.jpg', 1, '2025-05-06 09:04:32'),
(64, 5, 'Paneer Butter Masala', 'Rich tomato-based paneer curry', 259.00, 'images\\Paneer Butter Masal.jpg', 1, '2025-05-06 09:04:32'),
(65, 5, 'Chole Bhature', 'Spicy chickpeas with fried bread', 149.00, 'images\\Chole Bhature.jpg', 1, '2025-05-06 09:04:32'),
(66, 5, 'Dal Makhani', 'Slow-cooked black lentils', 199.00, 'images\\Dal Makhani.jpg', 1, '2025-05-06 09:04:32'),
(67, 5, 'Biryani', 'Spiced rice with meat or veggies', 279.00, 'images\\Biryani.jpg', 1, '2025-05-06 09:04:32'),
(68, 5, 'Rajma Chawal', 'Kidney beans with rice', 179.00, 'images\\Rajma Chawal.jpg', 1, '2025-05-06 09:04:32'),
(69, 5, 'Tandoori Chicken', 'Chargrilled spicy chicken', 299.00, 'images\\Tandoori Chicken.jpg', 1, '2025-05-06 09:04:32'),
(70, 5, 'Palak Paneer', 'Spinach and paneer curry', 239.00, 'images\\Palak Paneer.jpg', 1, '2025-05-06 09:04:32'),
(71, 5, 'Aloo Paratha', 'Stuffed potato flatbread', 129.00, 'images\\Aloo Paratha.jpg', 1, '2025-05-06 09:04:32'),
(72, 5, 'Kadhai Chicken', 'Chicken in spiced gravy', 289.00, 'images\\Kadhai Chicken;.jpg', 1, '2025-05-06 09:04:32'),
(74, 6, 'Rasgulla', 'Soft syrup-soaked sweet balls', 99.00, 'images\\Rasgulla.jpg', 1, '2025-05-06 09:04:32'),
(75, 6, 'Ice Cream Sundae', 'Layered ice cream dessert', 149.00, 'images\\Ice Cream Sundae.jpg', 1, '2025-05-06 09:04:32'),
(76, 6, 'Chocolate Cake', 'Rich and moist chocolate cake', 179.00, 'images\\Chocolate Cake.jpg', 1, '2025-05-06 09:04:32'),
(77, 6, 'Brownie', 'Chocolate brownie with nuts', 129.00, 'images\\Brownie.jpg', 1, '2025-05-06 09:04:32'),
(78, 6, 'Cheesecake', 'Creamy cheese-based dessert', 199.00, 'images\\Cheesecake.jpg', 1, '2025-05-06 09:04:32'),
(79, 6, 'Kheer', 'Indian rice pudding', 109.00, 'images\\Kheer.jpg', 1, '2025-05-06 09:04:32'),
(80, 6, 'Falooda', 'Cold dessert with noodles and ice cream', 139.00, 'images\\Falooda.jpg', 1, '2025-05-06 09:04:32'),
(81, 6, 'Mango Mousse', 'Fluffy mango dessert', 159.00, 'images\\Mango Mousse.jpg', 1, '2025-05-06 09:04:32'),
(82, 6, 'Fruit Salad', 'Chopped fresh fruits', 119.00, 'images\\Fruit Salad.jpg', 1, '2025-05-06 09:04:32');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','delivered','cancelled','completed') DEFAULT 'pending',
  `delivery_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `delivery_address`, `created_at`) VALUES
(9, 5, 509.85, 'completed', 'dsgfdfg', '2025-05-09 04:56:21'),
(10, 5, 238.95, 'completed', 'aXs', '2025-05-09 05:05:33'),
(11, 7, 374.40, 'completed', 'Tadong', '2025-05-19 05:13:24');

-- --------------------------------------------------------

--
-- Table structure for table `order_delivery`
--

CREATE TABLE `order_delivery` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `pickup_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delivery_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('assigned','picked_up','delivered') DEFAULT 'assigned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `food_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `food_item_id`, `quantity`, `price`, `created_at`) VALUES
(4, 9, 77, 1, 129.00, '2025-05-09 04:56:21'),
(5, 9, 39, 1, 199.00, '2025-05-09 04:56:21'),
(6, 9, 71, 1, 129.00, '2025-05-09 04:56:21'),
(7, 10, 39, 1, 199.00, '2025-05-09 05:05:33'),
(8, 11, 71, 1, 129.00, '2025-05-19 05:13:24'),
(9, 11, 39, 1, 199.00, '2025-05-19 05:13:24');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `phone`, `address`, `created_at`, `role`) VALUES
(2, 'admin', '123456', 'Admin', 'admin@gmail.com', '1234567890', 'for the user', '2025-05-05 09:56:35', 'admin'),
(5, 'rohi.t6460', '$2y$10$fC0qRnipgRB4HaWO1ugR.u5bhEFdoJQTlYCDAZNvau4U1wXtnqQc2', 'Rohit kumar', 'rohitkumarprasad151@gmail.com', '8371073629', 'kazi road gangtok', '2025-05-07 10:55:52', 'user'),
(6, 'mdkhatib', '$2y$10$cyE4gnMT9Glk1APqLaZRzOJbyib5eBoLCNITdBwRj9FDqlQlmN4ie', 'Md khatib', 'remilasharma685@gmail.com', '7894561387', 'fdgbfhhgf', '2025-05-14 06:45:20', 'user'),
(7, 'Dhiraj2180', '$2y$10$GICWE8xBx8322FOjkJPIQ.kKC.no2kKeIRlDiKn2oBnyaMXeIORm6', 'Dhiraj Jaiswal', 'user123@gmail.com', '1234567890', 'jhfbsd', '2025-05-19 05:09:53', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `user_promotions`
--

CREATE TABLE `user_promotions` (
  `user_id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_partners`
--
ALTER TABLE `delivery_partners`
  ADD PRIMARY KEY (`partner_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_delivery`
--
ALTER TABLE `order_delivery`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `food_item_id` (`food_item_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_promotions`
--
ALTER TABLE `user_promotions`
  ADD PRIMARY KEY (`user_id`,`promotion_id`),
  ADD KEY `promotion_id` (`promotion_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `delivery_partners`
--
ALTER TABLE `delivery_partners`
  MODIFY `partner_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_delivery`
--
ALTER TABLE `order_delivery`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `food_items` (`id`);

--
-- Constraints for table `food_items`
--
ALTER TABLE `food_items`
  ADD CONSTRAINT `food_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_delivery`
--
ALTER TABLE `order_delivery`
  ADD CONSTRAINT `order_delivery_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_delivery_ibfk_2` FOREIGN KEY (`partner_id`) REFERENCES `delivery_partners` (`partner_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`food_item_id`) REFERENCES `food_items` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `food_items` (`id`);

--
-- Constraints for table `user_promotions`
--
ALTER TABLE `user_promotions`
  ADD CONSTRAINT `user_promotions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_promotions_ibfk_2` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
