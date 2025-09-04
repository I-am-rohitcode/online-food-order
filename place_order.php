<?php include 'db.php'; session_start();
$uid = $_SESSION['user']['id']; $phone = $_POST['phone']; $address = $_POST['address'];
foreach ($_SESSION['cart'] as $item) {
 $fid = $item['food_id']; $qty = $item['qty'];
 $conn->query("INSERT INTO orders (user_id, food_id, quantity, status, phone, address)
 VALUES ($uid, $fid, $qty, 'Processing', '$phone', '$address')");
} unset($_SESSION['cart']); echo 'Order placed!'; ?>