-- Create the Foodyo database
CREATE DATABASE IF NOT EXISTS foodyo;
USE foodyo;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Food items table
CREATE TABLE IF NOT EXISTS food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    food_item_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (food_item_id) REFERENCES food_items(id)
);

-- Insert sample categories
INSERT INTO categories (name, image) VALUES
('Pizza', 'images/pizza.jpg'),
('Burger', 'images/burger.jpg'),
('Pasta', 'images/pasta.jpg'),
('Sushi', 'images/sushi.jpg'),
('Indian', 'images/indian.jpg'),
('Desserts', 'images/desserts.jpg');

-- Insert sample food items
INSERT INTO food_items (category_id, name, description, price, image) VALUES
(1, 'Margherita Pizza', 'Fresh tomatoes, mozzarella, basil, and olive oil', 299.00, 'images/margherita-pizza.jpg'),
(1, 'Pepperoni Pizza', 'Classic pepperoni with extra cheese', 349.00, 'images/pepperoni-pizza.jpg'),
(2, 'Classic Burger', 'Juicy beef patty with lettuce and special sauce', 199.00, 'images/classic-burger.jpg'),
(2, 'Chicken Burger', 'Grilled chicken with fresh vegetables', 249.00, 'images/chicken-burger.jpg'),
(3, 'Pasta Carbonara', 'Creamy pasta with bacon and parmesan', 249.00, 'images/pasta-carbonara.jpg'),
(3, 'Pasta Alfredo', 'Fettuccine in rich cream sauce', 229.00, 'images/pasta-alfredo.jpg'),
(4, 'Sushi Platter', 'Assorted fresh sushi with soy sauce', 399.00, 'images/sushi-platter.jpg'),
(5, 'Butter Chicken', 'Tender chicken in rich tomato gravy', 349.00, 'images/butter-chicken.jpg'),
(5, 'Veg Biryani', 'Fragrant basmati rice with mixed vegetables', 299.00, 'images/veg-biryani.jpg'),
(6, 'Gulab Jamun', 'Sweet milk solids dumplings in sugar syrup', 149.00, 'images/gulab-jamun.jpg'),
(6, 'Ice Cream', 'Vanilla ice cream with chocolate sauce', 99.00, 'images/ice-cream.jpg');

-- Insert sample user (password: test123)
INSERT INTO users (username, password, fullname, email, phone, address, role) VALUES
('testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', 'test@example.com', '1234567890', '123 Test Street, Test City', 'user'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin@foodyo.com', '9876543210', 'Admin Office, Foodyo HQ', 'admin');

-- Cart table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    item_id INT,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES food_items(id)
);

-- Reviews table
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    item_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES food_items(id)
);

-- Delivery Partners table
CREATE TABLE delivery_partners (
    partner_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    vehicle_number VARCHAR(20),
    status ENUM('available', 'busy', 'offline') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order Delivery table
CREATE TABLE order_delivery (
    delivery_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    partner_id INT,
    pickup_time TIMESTAMP,
    delivery_time TIMESTAMP,
    status ENUM('assigned', 'picked_up', 'delivered') DEFAULT 'assigned',
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (partner_id) REFERENCES delivery_partners(partner_id)
);

-- Promotions table
CREATE TABLE promotions (
    promotion_id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(10,2),
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Promotions table (to track which users have used which promotions)
CREATE TABLE user_promotions (
    user_id INT,
    promotion_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, promotion_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (promotion_id) REFERENCES promotions(promotion_id)
); 