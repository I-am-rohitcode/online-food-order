<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
$orders_query = "SELECT o.*, 
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o 
                WHERE o.user_id = ? 
                ORDER BY o.created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];

while ($order = $result->fetch_assoc()) {
    $orders[] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Foodyo</title>
    <link rel="shortcut icon" href="images/tablogo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            max-width: 1000px;
            margin: 100px auto 20px;
            padding: 20px;
        }

        .orders-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .orders-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .orders-list {
            display: grid;
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .view-order-btn {
            padding: 6px 12px;
            background: #e91e63;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .view-order-btn:hover {
            background: #c2185b;
            transform: translateY(-2px);
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 500;
            color: #333;
        }

        .empty-orders {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-orders i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-orders h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .order-menu-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #e91e63;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .order-menu-btn:hover {
            background: #c2185b;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .orders-container {
                margin: 80px auto 20px;
                padding: 15px;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-actions {
                width: 100%;
                justify-content: space-between;
            }

            .order-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="menu.php" class="order-menu-btn">Order Now</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h5>Order #<?php echo $order['id']; ?></h5>
                            <div class="order-actions">
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" class="view-order-btn">
                                    <i class="fas fa-eye"></i> View Order
                                </a>
                            </div>
                        </div>
                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Order Date</span>
                                <span class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Items</span>
                                <span class="detail-value"><?php echo $order['item_count']; ?> items</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Amount</span>
                                <span class="detail-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
