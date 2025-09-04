<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle different cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            addToCart($conn, $user_id);
            break;
        case 'update':
            updateCart($conn, $user_id);
            break;
        case 'remove':
            removeFromCart($conn, $user_id);
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    // Display cart page
    displayCart($conn, $user_id);
}

function addToCart($conn, $user_id) {
    if (!isset($_POST['item_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Item ID is required']);
        return;
    }

    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'] ?? 1;

    // Check if item exists and is available
    $check_item = $conn->prepare("SELECT id, price FROM food_items WHERE id = ? AND is_available = 1");
    $check_item->bind_param("i", $item_id);
    $check_item->execute();
    $item_result = $check_item->get_result();

    if ($item_result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Item not available']);
        return;
    }

    // Check if item already exists in cart
    $check_cart = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND item_id = ?");
    $check_cart->bind_param("ii", $user_id, $item_id);
    $check_cart->execute();
    $cart_result = $check_cart->get_result();

    if ($cart_result->num_rows > 0) {
        // Update quantity
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?");
        $update->bind_param("iii", $new_quantity, $user_id, $item_id);
        $update->execute();
    } else {
        // Add new item
        $insert = $conn->prepare("INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $user_id, $item_id, $quantity);
        $insert->execute();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
}

function updateCart($conn, $user_id) {
    if (!isset($_POST['item_id']) || !isset($_POST['quantity'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Item ID and quantity are required']);
        return;
    }

    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];

    if ($quantity <= 0) {
        // Remove item if quantity is 0 or negative
        removeFromCart($conn, $user_id);
        return;
    }

    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?");
    $update->bind_param("iii", $quantity, $user_id, $item_id);
    $update->execute();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Cart updated']);
}

function removeFromCart($conn, $user_id) {
    if (!isset($_POST['item_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Item ID is required']);
        return;
    }

    $item_id = $_POST['item_id'];

    $delete = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND item_id = ?");
    $delete->bind_param("ii", $user_id, $item_id);
    $delete->execute();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
}

function displayCart($conn, $user_id) {
    // Fetch cart items with food details
    $query = "SELECT c.*, f.name, f.price, f.image 
              FROM cart c 
              JOIN food_items f ON c.item_id = f.id 
              WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($query);
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
    $cgst = $total * 0.025; // 2.5% CGST
    $sgst = $total * 0.025; // 2.5% SGST
    $total += $cgst + $sgst;
    $final_total = $total + $cgst + $sgst + 30;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Foodyo</title>
    <link rel="shortcut icon" href="images/tablogo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 100px auto 20px;
            padding: 20px;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-items {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 20px;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-name {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .item-price {
            color: #e91e63;
            font-weight: 500;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 20px;
        }

        .quantity-btn {
            background: #f5f5f5;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #e91e63;
            color: white;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }

        .remove-btn {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            color: #c82333;
        }

        .cart-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .total-row {
            font-size: 1.2rem;
            font-weight: 600;
            color: #e91e63;
        }

        .checkout-btn {
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

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.3);
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .continue-shopping {
            color: #e91e63;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            border: 2px solid #e91e63;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .continue-shopping:hover {
            background: #e91e63;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="menu.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item" data-item-id="<?php echo $item['item_id']; ?>">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="quantity-controls">
                            <button class="quantity-btn decrease">-</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                            <button class="quantity-btn increase">+</button>
                        </div>
                        <button class="remove-btn" title="Remove item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span>₹30.00</span>
                </div>
                <div class="summary-row">
                    <span>CGST (2.5%)</span>
                    <span>₹<?php echo number_format($cgst, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>SGST (2.5%)</span>
                    <span>₹<?php echo number_format($sgst, 2); ?></span>
                </div>
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span>₹<?php echo number_format($final_total, 2); ?></span>
                </div>
                <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                    Proceed to Checkout
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.cart-item').forEach(item => {
            const itemId = item.dataset.itemId;
            const quantityInput = item.querySelector('.quantity-input');
            const decreaseBtn = item.querySelector('.decrease');
            const increaseBtn = item.querySelector('.increase');
            const removeBtn = item.querySelector('.remove-btn');

            // Update quantity
            function updateQuantity(newQuantity) {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('item_id', itemId);
                formData.append('quantity', newQuantity);

                fetch('card.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message);
                        // Reset quantity on error
                        quantityInput.value = quantityInput.defaultValue;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating cart');
                    quantityInput.value = quantityInput.defaultValue;
                });
            }

            // Remove item
            function removeItem() {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('item_id', itemId);

                fetch('card.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        // Reload page if cart is empty
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            location.reload();
                        }
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error removing item');
                });
            }

            // Event listeners
            decreaseBtn.addEventListener('click', () => {
                const newQuantity = parseInt(quantityInput.value) - 1;
                if (newQuantity >= 1) {
                    quantityInput.value = newQuantity;
                    updateQuantity(newQuantity);
                }
            });

            increaseBtn.addEventListener('click', () => {
                const newQuantity = parseInt(quantityInput.value) + 1;
                quantityInput.value = newQuantity;
                updateQuantity(newQuantity);
            });

            quantityInput.addEventListener('change', () => {
                const newQuantity = parseInt(quantityInput.value);
                if (newQuantity >= 1) {
                    updateQuantity(newQuantity);
                } else {
                    quantityInput.value = 1;
                    updateQuantity(1);
                }
            });

            removeBtn.addEventListener('click', removeItem);
        });
    </script>
</body>
</html>
<?php
}
?>