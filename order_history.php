<?php include 'db.php'; session_start();
$uid = $_SESSION['user']['id'];
$res = $conn->query("SELECT orders.*, food.name as fname FROM orders JOIN food ON food.id = orders.food_id WHERE user_id = $uid");
while ($row = $res->fetch_assoc()) {
 echo "{$row['fname']} x {$row['quantity']} - {$row['status']}<br>Address: {$row['address']}<br><br>";
} ?>