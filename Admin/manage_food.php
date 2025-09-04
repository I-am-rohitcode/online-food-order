<?php include '../db.php'; session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$name = $_POST['name']; $price = $_POST['price'];
$conn->query("INSERT INTO food (name, price) VALUES ('$name', $price)");
}
$res = $conn->query("SELECT * FROM food");
while ($row = $res->fetch_assoc()) {
 echo "{$row['name']} - ₹{$row['price']}<br>";
} ?><form method='post'>
Name: <input type='text' name='name'><br>
Price: <input type='number' name='price'><br>
<button type='submit'>Add Food</button></form>