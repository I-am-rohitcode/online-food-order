
<?php
// session_start();

// if (!isset($_SESSION['user_id'])) {
//     // User not logged in
//     header("Location:login.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foodyo - Online Food Order</title>
    <link rel="shortcut icon" href="images/tablogo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8f8f8;
        }

        .navbar {
            background: linear-gradient(90deg, #f44336, #e91e63);
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
           
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo a {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-img {
            height: 60px;
            border-radius: 50%;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            align-items: center;
        }

        .nav-links li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .nav-links li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .login-btn {
            color: #e91e63;
            padding: 0.7rem 1.5rem;
            border-radius: 5px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .login-btn:hover {
            background-color: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
        }

        .menu-toggle {
            display: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            background: none;
            border: none;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: linear-gradient(90deg, #f44336, #e91e63);
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li a {
                width: 100%;
                text-align: center;
                padding: 0.8rem;
            }

            .menu-toggle {
                display: block;
            }

            .logo a {
                font-size: 1.5rem;
            }

            .logo-img {
                width: 32px;
                height: 32px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.jpeg" alt="Foodyo Logo" class="logo-img">
                    <span>Foodyo</span>
                </a>
            </div>
            <button class="menu-toggle" onclick="toggleMenu()">☰</button>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="Card.php">Card</a></li>
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php" class="login-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="login-btn">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById('navLinks');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (!navLinks.contains(event.target) && !menuToggle.contains(event.target)) {
                navLinks.classList.remove('active');
            }
        });
    </script>
</body>
</html>




