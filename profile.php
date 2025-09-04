<?php
session_start();
include 'navbar.php';
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Initialize counts
$order_count = 0;
$fav_count = 0;
$review_count = 0;

// Get user's order history
$order_stmt = $conn->prepare("
    SELECT o.*, COUNT(oi.id) as total_items, SUM(oi.quantity * oi.price) as total_amount 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();
$order_count = $orders->num_rows;

// Get user's favorite items count
// $fav_stmt = $conn->prepare("SELECT COUNT(*) as fav_count FROM favorites WHERE user_id = ?");
// $fav_stmt->bind_param("i", $user_id);
// $fav_stmt->execute();
// $fav_result = $fav_stmt->get_result();
// if($fav_result) {
//     $fav_count = $fav_result->fetch_assoc()['fav_count'];
// }

// Get user's reviews count
$review_stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE user_id = ?");
$review_stmt->bind_param("i", $user_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
if($review_result) {
    $review_count = $review_result->fetch_assoc()['review_count'];
}

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Foodyo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-box">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?></h2>
                <p class="username">@<?php echo htmlspecialchars($user['username'] ?? 'username'); ?></p>
            </div>

            <div class="profile-info">
                <div class="info-group">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo htmlspecialchars($user['email'] ?? 'No email provided'); ?></span>
                </div>
                <div class="info-group">
                    <i class="fas fa-phone"></i>
                    <span><?php echo htmlspecialchars($user['phone'] ?? 'No phone provided'); ?></span>
                </div>
                <div class="info-group">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($user['address'] ?? 'No address provided'); ?></span>
                </div>
                <div class="info-group">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Member since <?php echo isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'N/A'; ?></span>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="stat-value"><?php echo $order_count; ?></span>
                    <span class="stat-label">Orders</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-heart"></i>
                    <span class="stat-value"><?php echo $fav_count; ?></span>
                    <span class="stat-label">Favorites</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-star"></i>
                    <span class="stat-value"><?php echo $review_count; ?></span>
                    <span class="stat-label">Reviews</span>
                </div>
            </div>

            <div class="order-history">
                <h3>Order History</h3>
                <?php if ($order_count > 0): ?>
                    <div class="orders-list">
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <span class="order-id">Order #<?php echo $order['id']; ?></span>
                                        <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <span class="order-status <?php echo strtolower($order['status'] ?? 'pending'); ?>">
                                        <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
                                    </span>
                                </div>
                                <div class="order-details">
                                    <div class="order-items">
                                        <i class="fas fa-box"></i>
                                        <span><?php echo $order['total_items'] ?? 0; ?> items</span>
                                    </div>
                                    <div class="order-total">
                                        <i class="fas fa-rupee-sign"></i>
                                        <span><?php echo number_format($order['total_amount'] ?? 0, 2); ?></span>
                                    </div>
                                </div>
                                <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" class="view-order-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag"></i>
                        <p>No orders yet</p>
                        <a href="menu.php" class="start-ordering-btn">Start Ordering</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-actions">
                <!-- <a href="edit_profile.php" class="edit-profile-button">
                    <i class="fas fa-edit"></i> Edit Profile
                </a> -->
                <a href="change_password.php" class="change-password-button">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>
    </div>

    <?php
    // Debug output
    if(isset($_SESSION['user_id'])) {
        echo "<!-- Debug: User ID: " . $_SESSION['user_id'] . " -->";
    }
    ?>
</body>
</html>
