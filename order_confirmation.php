<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Fetch order details
$order_query = "SELECT o.*, u.username, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Fetch order items
$items_query = "SELECT oi.*, f.name, f.image 
                FROM order_items oi 
                JOIN food_items f ON oi.food_item_id = f.id 
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = [];

while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Foodyo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .confirmation-container {
            max-width: 1000px;
            margin: 100px auto 20px;
            padding: 20px;
        }

        .order-details {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .confirmation-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(45deg, #f44336, #e91e63);
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.2);
        }

        .success-icon {
            color: white;
            font-size: 4rem;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            70% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .confirmation-message {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .order-number {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .order-header h5 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .order-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .view-order-btn {
            padding: 8px 20px;
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge i {
            font-size: 0.9rem;
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

        .details-section {
            margin-bottom: 35px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
        }

        .details-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #e91e63;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            width: 180px;
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            flex: 1;
            font-weight: 500;
            color: #333;
        }

        .order-items {
            display: grid;
            gap: 15px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .order-item:hover {
            transform: translateY(-2px);
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 8px;
        }

        .item-price {
            color: #e91e63;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .item-quantity {
            color: #666;
            font-size: 0.95rem;
            margin-top: 5px;
        }

        .order-summary {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-top: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 2px solid #eee;
            border-bottom: none;
            font-weight: 600;
            color: #e91e63;
            font-size: 1.1rem;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 35px;
        }

        .action-btn {
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }

        .view-orders-btn {
            background: linear-gradient(45deg, #f44336, #e91e63);
            color: white;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.2);
        }

        .view-orders-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(233, 30, 99, 0.3);
        }

        .home-btn {
            background: white;
            color: #333;
            border: 2px solid #eee;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .home-btn:hover {
            background: #f8f9fa;
            border-color: #e91e63;
            color: #e91e63;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .confirmation-container {
                margin: 80px auto 20px;
                padding: 15px;
            }

            .confirmation-header {
                padding: 20px;
            }

            .confirmation-message {
                font-size: 1.5rem;
            }

            .order-header {
                flex-direction: column;
                gap: 15px;
            }

            .order-actions {
                width: 100%;
                justify-content: space-between;
            }

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .detail-label {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }

            .order-item {
                flex-direction: column;
                text-align: center;
            }

            .item-image {
                margin: 0 0 15px 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="confirmation-container">
        <div class="confirmation-header">
            <i class="fas fa-check-circle success-icon"></i>
            <h1 class="confirmation-message">Order Placed Successfully!</h1>
            <p class="order-number">Order #<?php echo str_pad($order_id, 8, '0', STR_PAD_LEFT); ?></p>
        </div>

        <div class="order-details">
            <div class="order-header">
                <h5>Order #<?php echo $order['id']; ?></h5>
                <div class="order-actions">
                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                        <i class="fas fa-circle"></i>
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                    <!-- <a href="view_order.php?id=<?php echo $order['id']; ?>" class="view-order-btn">
                        <i class="fas fa-eye"></i> View Order
                    </a> -->
                </div>
            </div>

            <div class="details-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Order Information
                </h2>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Status:</span>
                    <span class="detail-value"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">Cash on Delivery</span>
                </div>
            </div>

            <div class="details-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Delivery Details
                </h2>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['username']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                </div>
            </div>

            <div class="details-section">
                <h2 class="section-title">
                    <i class="fas fa-utensils"></i>
                    Order Items
                </h2>
                <div class="order-items">
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                <div class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($order['total_amount'] - 30, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>₹30.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Amount</span>
                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="orders.php" class="action-btn view-orders-btn">
                <i class="fas fa-list"></i>
                View All Orders
            </a>
            <a href="index.php" class="action-btn home-btn">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
