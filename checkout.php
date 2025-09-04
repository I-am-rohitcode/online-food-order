<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch cart items with food details
$cart_query = "SELECT c.*, f.name, f.price, f.image 
              FROM cart c 
              JOIN food_items f ON c.item_id = f.id 
              WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$items = [];

while ($item = $result->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items[] = $item;
}

// Calculate taxes and final total
$cgst = $total * 0.025; // 2.5% CGST
$sgst = $total * 0.025; // 2.5% SGST
$delivery_fee = 30.00;
$final_total = $total + $cgst + $sgst + $delivery_fee;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['address', 'payment_method'];
    $error = false;
    $error_message = '';

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error = true;
            $error_message = "Please fill in all required fields.";
            break;
        }
    }

    if (!$error && empty($items)) {
        $error = true;
        $error_message = "Your cart is empty.";
    }

    if (!$error) {
        $address = trim($_POST['address']);
        $payment_method = $_POST['payment_method'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Create order
            $order_query = "INSERT INTO orders (user_id, total_amount, delivery_address, status, created_at) 
                           VALUES (?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($order_query);
            $stmt->bind_param("ids", $user_id, $final_total, $address);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating order: " . $stmt->error);
            }
            
            $order_id = $conn->insert_id;

            // Add order items
            $order_items_query = "INSERT INTO order_items (order_id, food_item_id, quantity, price, created_at) 
                                VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($order_items_query);

            foreach ($items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['item_id'], $item['quantity'], $item['price']);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding order items: " . $stmt->error);
                }
            }

            // Clear cart
            $clear_cart = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($clear_cart);
            $stmt->bind_param("i", $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error clearing cart: " . $stmt->error);
            }

            // Commit transaction
            $conn->commit();

            // Redirect to order confirmation
            header("Location: order_confirmation.php?order_id=" . $order_id);
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = true;
            $error_message = "Error placing order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Foodyo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 100px auto 20px;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #e91e63;
            outline: none;
        }

        .payment-methods {
            margin-top: 30px;
        }

        .payment-method {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #e91e63;
        }

        .payment-method input[type="radio"] {
            margin-right: 10px;
        }

        .payment-method i {
            margin-right: 10px;
            font-size: 1.2rem;
            color: #666;
        }

        .order-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            position: sticky;
            top: 100px;
        }

        .summary-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .place-order-btn {
            background: linear-gradient(90deg, #f44336, #e91e63);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.3);
        }

        .error-message {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="checkout-container">
        <div class="checkout-form">
            <h2>Delivery Details</h2>
            <?php if (isset($error) && $error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="address">Delivery Address</label>
                    <textarea id="address" name="address" rows="3" required placeholder="Enter your complete delivery address"></textarea>
                </div>

                <div class="payment-methods">
                    <h3>Payment Method</h3>
                    <div class="payment-method">
                        <input type="radio" id="cod" name="payment_method" value="cod" checked>
                        <i class="fas fa-money-bill-wave"></i>
                        <label for="cod">Cash on Delivery</label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="card" name="payment_method" value="card">
                        <i class="fas fa-credit-card"></i>
                        <label for="card">Credit/Debit Card</label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="upi" name="payment_method" value="upi">
                        <i class="fas fa-mobile-alt"></i>
                        <label for="upi">UPI</label>
                    </div>
                </div>

                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>

        <div class="order-summary">
            <h3 class="summary-title">Order Summary</h3>
            <?php if (empty($items)): ?>
                <div class="empty-cart-message">Your cart is empty</div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                        <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="summary-item">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-item">
                    <span>CGST (2.5%)</span>
                    <span>₹<?php echo number_format($cgst, 2); ?></span>
                </div>
                <div class="summary-item">
                    <span>SGST (2.5%)</span>
                    <span>₹<?php echo number_format($sgst, 2); ?></span>
                </div>
                <div class="summary-item">
                    <span>Delivery Fee</span>
                    <span>₹<?php echo number_format($delivery_fee, 2); ?></span>
                </div>
                <div class="summary-item" style="font-weight: 600; color: #e91e63;">
                    <span>Total</span>
                    <span>₹<?php echo number_format($final_total, 2); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add payment method selection handling
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', () => {
                const radio = method.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });

        // Form validation
        function validateForm() {
            const address = document.getElementById('address').value.trim();
            if (!address) {
                alert('Please enter your delivery address');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>