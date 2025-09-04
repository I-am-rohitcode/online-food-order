<?php
session_start();
require_once '../config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$error = null;
$message = null;
$debug_info = [];

// Handle order status update
if (isset($_POST['update_status'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid request";
    } else {
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $new_status = htmlspecialchars($_POST['status'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($new_status, $valid_statuses)) {
            $error = "Invalid status value";
        } else {
            $query = "UPDATE orders SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("si", $new_status, $order_id);
                if ($stmt->execute()) {
                    $message = "Order status updated successfully!";
                } else {
                    $error = "Error updating order status: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Handle export to CSV
if (isset($_POST['export_csv'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid request";
    } else {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order ID', 'Customer', 'Total Amount', 'Status', 'Date', 'Items']);
        
        $export_query = "SELECT o.*, u.username as customer_name,
                        GROUP_CONCAT(CONCAT(f.name, ' (', oi.quantity, ')') SEPARATOR ', ') as order_items
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        LEFT JOIN order_items oi ON o.id = oi.order_id
                        LEFT JOIN food_items f ON oi.food_item_id = f.id
                        GROUP BY o.id
                        ORDER BY o.created_at DESC";
        
        $result = $conn->query($export_query);
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['customer_name'],
                $row['total_amount'],
                $row['status'],
                $row['created_at'],
                $row['order_items']
            ]);
        }
        fclose($output);
        exit();
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = '';

if ($status_filter) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($search) {
    $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($date_from) {
    $where_conditions[] = "o.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
    $types .= 's';
}

if ($date_to) {
    $where_conditions[] = "o.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total number of orders with filters
$count_query = "SELECT COUNT(DISTINCT o.id) as total 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id";
if (!empty($where_conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $where_conditions);
}

$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $records_per_page);

// Get orders with filters
$query = "SELECT o.*, u.username as customer_name, u.email, u.phone,
          GROUP_CONCAT(CONCAT(f.name, ' (', oi.quantity, ')') SEPARATOR ', ') as order_items
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN food_items f ON oi.food_item_id = f.id";

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $records_per_page;
$params[] = $offset;

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        $error = "Error executing query: " . $stmt->error;
    }
    $orders = $stmt->get_result();
}

// Get order statistics
$stats_query = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(total_amount) as total_revenue
                FROM orders";
$stats = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Foodyo Admin</title>
    <link rel="shortcut icon" href="images/tablogo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background: #f5f6fa;
        }

        .page-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin: 0;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filters form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            color: #2c3e50;
            background-color: white;
            transition: all 0.3s ease;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #e91e63;
            box-shadow: 0 0 0 2px rgba(233, 30, 99, 0.1);
        }

        .filter-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%232c3e50' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 30px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(90deg, #f44336, #e91e63);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #e91e63, #c2185b);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #e91e63;
            color: #e91e63;
            transform: translateY(-2px);
        }

        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .order-id {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .order-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .order-info {
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .info-label {
            color: #7f8c8d;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
        }

        .order-items {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .item-list li {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .total-label {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .total-value {
            font-size: 1.2rem;
            color: #e91e63;
            font-weight: 600;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .status-select {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            background: white;
        }

        .status-select:focus {
            outline: none;
            border-color: #e91e63;
        }

        .update-btn {
            background: #e91e63;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .update-btn:hover {
            background: #c2185b;
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

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .page-link {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: #f8f9fa;
            border-color: #e91e63;
            color: #e91e63;
        }

        .page-link.active {
            background: #e91e63;
            color: white;
            border-color: #e91e63;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 70px;
            }

            .filters form {
                flex-direction: column;
                gap: 15px;
            }

            .filter-group {
                width: 100%;
            }

            .filter-actions {
                width: 100%;
                justify-content: stretch;
            }

            .btn {
                flex: 1;
            }

            .orders-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="orders-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Foodyo Admin</h2>
            </div>
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_orders.php" class="nav-link active">
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
            <div class="page-header">
                <h1 class="page-title">Manage Orders</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Order Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?php echo number_format($stats['total_orders']); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending Orders</h3>
                    <div class="value"><?php echo number_format($stats['pending_orders']); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Processing Orders</h3>
                    <div class="value"><?php echo number_format($stats['processing_orders']); ?></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <form method="GET" action="">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by customer or order ID">
                    </div>
                    <div class="filter-group">
                        <label for="date_from">Date From</label>
                        <input type="date" name="date_from" id="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="filter-group">
                        <label for="date_to">Date To</label>
                        <input type="date" name="date_to" id="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="admin_orders.php" class="btn btn-secondary">Reset</a>
                        <!-- <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <button type="submit" name="export_csv" class="btn btn-secondary">Export CSV</button>
                        </form> -->
                    </div>
                </form>
            </div>

            <!-- Orders Grid -->
            <div class="orders-grid">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-number">Order #<?php echo $order['id']; ?></div>
                                <div class="order-date"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></div>
                            </div>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>

                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Delivery Address:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                            </div>
                            <div class="order-items">
                                <h4>Order Items:</h4>
                                <p><?php echo htmlspecialchars($order['order_items'] ?? 'No items found'); ?></p>
                            </div>
                        </div>

                        <div class="customer-info">
                            <h4>Customer Details</h4>
                            <div class="customer-details">
                                <div>Name: <?php echo htmlspecialchars($order['customer_name']); ?></div>
                                <div>Email: <?php echo htmlspecialchars($order['email']); ?></div>
                                <div>Phone: <?php echo htmlspecialchars($order['phone']); ?></div>
                            </div>
                        </div>

                        <div class="order-actions">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="update-btn">
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($total_pages > 1): ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" 
                           class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>