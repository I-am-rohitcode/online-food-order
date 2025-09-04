<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch dashboard statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COUNT(*) FROM orders WHERE status = 'pending') as pending_orders,
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM food_items) as total_items,
    (SELECT SUM(total_amount) FROM orders WHERE status = 'completed') as total_revenue";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Fetch recent orders
$recent_orders_query = "SELECT o.*, u.username, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC 
                       LIMIT 5";
$recent_orders = $conn->query($recent_orders_query);

// Fetch popular items
$popular_items_query = "SELECT f.*, COUNT(oi.id) as order_count 
                       FROM food_items f 
                       LEFT JOIN order_items oi ON f.id = oi.food_item_id 
                       GROUP BY f.id 
                       ORDER BY order_count DESC 
                       LIMIT 5";
$popular_items = $conn->query($popular_items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Foodyo</title>
    <link rel="shortcut icon" href="images/tablogo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 200px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #e91e63;
        }

        .nav-menu {
            margin-top: 30px;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: #34495e;
            color: #e91e63;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        } */

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background: #f5f6fa;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .welcome-text {
            font-size: 1.5rem;
            color: #2c3e50;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .stat-icon.orders { background: #e3f2fd; color: #1976d2; }
        .stat-icon.users { background: #e8f5e9; color: #388e3c; }
        .stat-icon.items { background: #fff3e0; color: #f57c00; }
        .stat-icon.revenue { background: #fce4ec; color: #c2185b; }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .card-title {
            font-size: 1.2rem;
            color: #2c3e50;
            margin: 0;
        }

        .view-all {
            color: #e91e63;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .order-list, .item-list {
            display: grid;
            gap: 15px;
        }

        .order-item, .item-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .order-item:hover, .item-card:hover {
            transform: translateX(5px);
        }

        .order-info, .item-info {
            flex: 1;
        }

        .order-title, .item-title {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .order-meta, .item-meta {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .item-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }

        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar-header h2 {
                display: none;
            }

            .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Foodyo Admin</h2>
            </div>
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_orders.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_menu.php" class="nav-link">
                        <i class="fas fa-utensils"></i>
                        <span>Menu Items</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_categories.php" class="nav-link">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </div>
                <!-- <div class="nav-item">
                    <a href="admin_settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div> -->
                <div class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="welcome-text">Welcome, Admin!</h1>
                <div class="date"><?php echo date('F j, Y'); ?></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon items">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_items']; ?></div>
                    <div class="stat-label">Menu Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Orders</h2>
                        <a href="admin_orders.php" class="view-all">View All</a>
                    </div>
                    <div class="order-list">
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-title">Order #<?php echo $order['id']; ?></div>
                                    <div class="order-meta">
                                        <?php echo htmlspecialchars($order['username']); ?> • 
                                        ₹<?php echo number_format($order['total_amount'], 2); ?>
                                    </div>
                                </div>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h2 class="card-title">Popular Items</h2>
                        <a href="admin_menu.php" class="view-all">View All</a>
                    </div>
                    <div class="item-list">
                        <?php while ($item = $popular_items->fetch_assoc()): ?>
                            <div class="item-card">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                                <div class="item-info">
                                    <div class="item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-meta">
                                        <?php echo $item['order_count']; ?> orders • 
                                        ₹<?php echo number_format($item['price'], 2); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 