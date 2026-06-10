<?php
include '_db_connect.php';

echo "Sum from delivered_orders:\n";
$res1 = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM delivered_orders");
$row1 = mysqli_fetch_assoc($res1);
echo $row1['total'] . "\n\n";

echo "Sum from order_table (status='Delivered'):\n";
$res2 = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM order_table WHERE order_status='Delivered'");
$row2 = mysqli_fetch_assoc($res2);
echo $row2['total'] . "\n\n";

echo "Sum from shops (total_sales):\n";
$res3 = mysqli_query($conn, "SELECT SUM(total_sales) as total FROM shops");
$row3 = mysqli_fetch_assoc($res3);
echo $row3['total'] . "\n\n";

echo "Content of delivered_orders:\n";
$res4 = mysqli_query($conn, "SELECT * FROM delivered_orders");
while($row = mysqli_fetch_assoc($res4)) {
    print_r($row);
}
?>
