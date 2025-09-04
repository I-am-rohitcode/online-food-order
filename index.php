<?php
session_start();
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="shortcut icon" href="images\tablogo.png" type="image/png"> -->
    <title>Foodyo - Delicious Food Delivery</title>
    <link rel="shortcut icon" href="images/tablogo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
 
    <style>
        .hero {
  background: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.8)),
    url('images/poster.jpeg');
background-position: center;
}

        .simple-card button a{
            text-decoration: none;
            color:white;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Delicious Food Delivered To Your Doorstep</h1>
            <p>Order your favorite meals from the best restaurants in town</p>
            <a href="menu.php" class="cta-button">Order Now</a>
        </div>
    </section>
    <section class="categories">
        <div class="categories-container">
            <h2 class="section-title">Popular Categories</h2>
            <div class="categories-grid">
                <div class="category-card">
                    <img src="images\margherita-pizza.jpg" alt="Pizza">
                    <h3>Pizza</h3>
                </div>
                <div class="category-card">
                    <img src="images\Cheese Burger.jpg" alt="Burger">
                    <h3>Burger</h3>
                </div>
                <div class="category-card">
                    <img src="images\Garlic Butter Pasta.jpg" alt="Pasta">
                    <h3>Pasta</h3>
                </div>
                <div class="category-card">
                    <img src="images\sushi-platter.jpg" alt="Sushi">
                    <h3>Sushi</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Simple Food Cards -->
    <section class="simple-food">
        <div class="simple-food-container">
            <h2 class="section-title">Our Menu</h2>
            <div class="simple-grid">
                <div class="simple-card">
                    <img src="images/butter-chicken.jpg" alt="Butter Chicken">
                    <div class="simple-content">
                        <h3>Butter Chicken</h3>
                        <p>₹349</p>
                        <button><a href="menu.php">Order Now</a></button>
                    </div>
                </div>

                <div class="simple-card">
                    <img src="images/veg-biryani.jpg" alt="Veg Biryani">
                    <div class="simple-content">
                        <h3>Veg Biryani</h3>
                        <p>₹299</p>
                        <button><a href="menu.php">Order Now</a></button>
                    </div>
                </div>

                <div class="simple-card">
                    <img src="images\Paneer Tikka.jpg" alt="Paneer Tikka">
                    <div class="simple-content">
                        <h3>Paneer Tikka</h3>
                        <p>₹249</p>
                        <button><a href="menu.php">Order Now</a></button>
                    </div>
                </div>

                <div class="simple-card">
                    <img src="images/gulab-jamun.jpg" alt="Gulab Jamun">
                    <div class="simple-content">
                        <h3>Gulab Jamun</h3>
                        <p>₹149</p>
                        <button><a href="menu.php">Order Now</a></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

   

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="how-it-works-container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps-container">
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Choose Your Food</h3>
                    <p>Browse through our menu and select your favorite dishes</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Place Order</h3>
                    <p>Add items to cart and complete your order</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Get your food delivered hot and fresh</p>
                </div>
            </div>
        </div>
    </section>
     <!-- Features Section -->
     <!-- <section class="features">
        <div class="features-container">
            <div class="feature-card">
                <i class="fas fa-utensils"></i>
                <h3>Fresh Food</h3>
                <p>Delicious and fresh food prepared by expert chefs</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-truck"></i>
                <h3>Fast Delivery</h3>
                <p>Quick delivery to your location within 30 minutes</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-star"></i>
                <h3>Best Quality</h3>
                <p>High-quality ingredients and excellent service</p>
            </div>
        </div>
    </section> -->

    <!-- Special Offers Section -->
    <section class="special-offers">
        <div class="special-offers-container">
            <h2 class="section-title">Special Offers</h2>
            <div class="offers-grid">
                <div class="offer-card">
                    <div class="offer-content">
                        <h3>First Order Discount</h3>
                        <p>Get 20% off on your first order</p>
                        <a href="menu.php" class="offer-button">Order Now</a>
                    </div>
                </div>
                <div class="offer-card">
                    <div class="offer-content">
                        <h3>Weekend Special</h3>
                        <p>Free delivery on weekends</p>
                        <a href="menu.php" class="offer-button">Order Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Food Categories -->
    

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="newsletter-container">
            <h2>Subscribe to Our Newsletter</h2>
            <p>Get updates on new menu items, special offers, and exclusive deals!</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Foodyo</h3>
                <p>Your favorite food delivery platform bringing delicious meals right to your doorstep. Quality food, fast delivery, and excellent service.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms & Conditions</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact Us</h3>
                <div class="contact-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>123 Food Street, City, Country</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-phone"></i>
                    <p>+1 234 567 8900</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-envelope"></i>
                    <p>info@foodyo.com</p>
                </div>
            </div>

            <div class="footer-section">
                <h3>Opening Hours</h3>
                <p>Monday - Friday: 9:00 AM - 10:00 PM</p>
                <p>Saturday - Sunday: 10:00 AM - 11:00 PM</p>
                <p>Holidays: 11:00 AM - 9:00 PM</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Foodyo. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>