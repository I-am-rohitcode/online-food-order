
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>
<?php
// session_start();
include 'navbar.php';
include 'config.php';

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Build the food items query
$food_query = "SELECT f.*, c.name as category_name 
               FROM food_items f 
               JOIN categories c ON f.category_id = c.id 
               WHERE 1=1";

if ($selected_category !== 'all') {
    $food_query .= " AND f.category_id = ?";
}

if (!empty($search_query)) {
    $food_query .= " AND (f.name LIKE ? OR f.description LIKE ?)";
}

$food_query .= " ORDER BY f.name";

$stmt = $conn->prepare($food_query);

// Bind parameters if needed
if ($selected_category !== 'all' && !empty($search_query)) {
    $search_param = "%$search_query%";
    $stmt->bind_param("iss", $selected_category, $search_param, $search_param);
} elseif ($selected_category !== 'all') {
    $stmt->bind_param("i", $selected_category);
} elseif (!empty($search_query)) {
    $search_param = "%$search_query%";
    $stmt->bind_param("ss", $search_param, $search_param);
}

$stmt->execute();
$food_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Foodyo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f8f8;
            color: #333;
            line-height: 1.6;
        }

        /* Menu Container */
        .menu-container {
            min-height: 100vh;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Menu Header */
        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            margin-top:4rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .menu-header h1 {
            font-size: 2rem;
            color: #333;
            font-weight: 600;
        }

        /* Search Bar */
        .search-bar {
            flex: 1;
            max-width: 400px;
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
        }

        .search-form input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-form input:focus {
            outline: none;
            border-color: #e91e63;
            box-shadow: 0 0 0 2px rgba(233, 30, 99, 0.1);
        }

        .search-form button {
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .search-form button:hover {
            transform: translateY(-2px);
        }

        /* Menu Content */
        /* .menu-content {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            height: calc(100vh - 150px);
        } */

        /* Categories Sidebar */
        /* .categories-sidebar {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: fit-content;
        } */

        .menu-content {
    display: flex;
    /*height: calc(100vh - 80px); */
    overflow: hidden;
}

.categories-sidebar {
    width: 250px; /* Set your desired width */
    flex-shrink: 0;
    background: #f8f8f8;
    padding: 20px;
    border-right: 1px solid #ddd;
    overflow-y: auto; /* Optional: scroll only if content exceeds */
}

.food-items-container {
    flex-grow: 1;
    overflow-y: auto;
    padding: 20px;
    background: #fff;
}

.food-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    width: 100%;
    gap: 20px;
    overflow-y: auto; 
}

        .categories-sidebar h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .category-list {
            list-style: none;
        }

        .category-list li {
            margin-bottom: 0.5rem;
        }

        .category-list a {
            display: block;
            padding: 0.8rem 1rem;
            color: #666;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .category-list a:hover {
            background: #f8f8f8;
            color: #e91e63;
        }

        .category-list li.active a {
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
        }

        /* Food Items Grid */
        .food-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            height: 100%;
            overflow-y: auto; 
            padding-right: 1rem;
        }

        /* Food Card */
        .food-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .food-card:hover {
            transform: translateY(-5px);
        }

        .food-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .food-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .food-card:hover .food-image img {
            transform: scale(1.1);
        }

        .food-price {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .food-info {
            padding: 1.5rem;
        }

        .food-info h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .food-info p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .food-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .food-category {
            color: #e91e63;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .add-to-cart {
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-to-cart:hover {
            transform: translateY(-2px);
        }

        .add-to-cart:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* No Items Found */
        .no-items {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .no-items i {
            font-size: 3rem;
            color: #e91e63;
            margin-bottom: 1rem;
        }

        .no-items h3 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .no-items p {
            color: #666;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            display: none;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        .notification.success {
            background-color: #4CAF50;
        }

        .notification.error {
            background-color: #f44336;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .scroll-to-top:hover {
            transform: translateY(-3px);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .menu-content {
                grid-template-columns: 1fr;
            }

            .categories-sidebar {
             width: 100px;
             padding: 0;
                position: relative;
                top: 0;
                margin-bottom: 2rem;
            }

            .food-items-grid {
                height: auto;
                overflow-y: visible;
                padding-right: 0;
            }
        }

        @media (max-width: 768px) {
            .menu-container {
                padding: 1rem;
            }

            .menu-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar {
                max-width: none;
            }

            .food-items-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .menu-header h1 {
                font-size: 1.5rem;
            }

            .food-card {
                margin: 0 auto;
                max-width: 320px;
            }
        }
    </style>
</head>
<body>
    <div class="menu-container">
        <div id="notification" class="notification"></div>
        <div class="menu-header">
            <h1>Our Menu</h1>
            <div class="search-bar">
                <form action="menu.php" method="GET" class="search-form">
                    <?php if ($selected_category !== 'all'): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Search for food..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="menu-content">
            <div class="categories-sidebar">
                <h3>Categories</h3>
                <ul class="category-list">
                    <li class="<?php echo $selected_category === 'all' ? 'active' : ''; ?>">
                        <a href="menu.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>">
                            All Items
                        </a>
                    </li>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <li class="<?php echo $selected_category == $category['id'] ? 'active' : ''; ?>">
                            <a href="menu.php?category=<?php echo $category['id']; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="food-items-grid">
                <?php if ($food_result->num_rows > 0): ?>
                    <?php while ($item = $food_result->fetch_assoc()): ?>
                        <div class="food-card">
                            <div class="food-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="food-price">₹<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                            <div class="food-info">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="food-meta">
                                    <span class="food-category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                    <form action="card.php" method="POST" class="add-to-cart-form">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="add-to-cart">
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-utensils"></i>
                        <h3>No items found</h3>
                        <p>Try adjusting your search or category filter</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Function to show notification
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Add to cart functionality
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                
                // Disable button while processing
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                
                fetch('card.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showNotification('Item added to cart successfully!', 'success');
                    } else {
                        showNotification(data.message || 'Error adding item to cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error adding item to cart. Please try again.', 'error');
                })
                .finally(() => {
                    // Re-enable button
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                });
            });
        });

        // Smooth scrolling for food items grid
        const foodItemsGrid = document.querySelector('.food-items-grid');
        let isScrolling = false;
        let startY;
        let scrollTop;

        foodItemsGrid.addEventListener('mousedown', (e) => {
            isScrolling = true;
            startY = e.pageY - foodItemsGrid.offsetTop;
            scrollTop = foodItemsGrid.scrollTop;
        });

        foodItemsGrid.addEventListener('mousemove', (e) => {
            if (!isScrolling) return;
            e.preventDefault();
            const y = e.pageY - foodItemsGrid.offsetTop;
            const walk = (y - startY) * 2;
            foodItemsGrid.scrollTop = scrollTop - walk;
        });

        foodItemsGrid.addEventListener('mouseup', () => {
            isScrolling = false;
        });

        foodItemsGrid.addEventListener('mouseleave', () => {
            isScrolling = false;
        });

        // Touch events for mobile
        foodItemsGrid.addEventListener('touchstart', (e) => {
            isScrolling = true;
            startY = e.touches[0].pageY - foodItemsGrid.offsetTop;
            scrollTop = foodItemsGrid.scrollTop;
        });

        foodItemsGrid.addEventListener('touchmove', (e) => {
            if (!isScrolling) return;
            const y = e.touches[0].pageY - foodItemsGrid.offsetTop;
            const walk = (y - startY) * 2;
            foodItemsGrid.scrollTop = scrollTop - walk;
        });

        foodItemsGrid.addEventListener('touchend', () => {
            isScrolling = false;
        });

        // Smooth scroll to top button
        const scrollToTopBtn = document.createElement('button');
        scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        scrollToTopBtn.className = 'scroll-to-top';
        document.body.appendChild(scrollToTopBtn);

        scrollToTopBtn.addEventListener('click', () => {
            foodItemsGrid.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Show/hide scroll to top button
        foodItemsGrid.addEventListener('scroll', () => {
            if (foodItemsGrid.scrollTop > 300) {
                scrollToTopBtn.style.display = 'flex';
            } else {
                scrollToTopBtn.style.display = 'none';
            }
        });
    </script>

    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            display: none;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        .notification.success {
            background-color: #4CAF50;
        }

        .notification.error {
            background-color: #f44336;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .scroll-to-top:hover {
            transform: translateY(-3px);
        }
    </style>
</body>
</html> 