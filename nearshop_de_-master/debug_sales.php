<?php
include '_db_connect.php';

$sql = "SELECT 
            s.shop_name, 
            s.shop_id,
            o.order_id, 
            o.order_status, 
            o.total_amount,
            oi.pr_id,
            p.pr_name,
            oi.quantity, 
            oi.unit_pr,
            (oi.quantity * oi.unit_pr) as line_total
        FROM order_items oi
        JOIN product p ON oi.pr_id = p.pr_id
        JOIN shops s ON p.shop_id = s.shop_id
        JOIN order_table o ON oi.order_id = o.order_id
        ORDER BY s.shop_id, o.order_id";

$res = mysqli_query($conn, $sql);

echo "<table border='1'><tr><th>Shop</th><th>Order ID</th><th>Status</th><th>Product</th><th>Qty</th><th>Unit Pr</th><th>Line Total</th></tr>";
while ($row = mysqli_fetch_assoc($res)) {
    echo "<tr>";
    echo "<td>" . $row['shop_name'] . " (" . $row['shop_id'] . ")</td>";
    echo "<td>" . $row['order_id'] . "</td>";
    echo "<td>" . $row['order_status'] . "</td>";
    echo "<td>" . $row['pr_name'] . "</td>";
    echo "<td>" . $row['quantity'] . "</td>";
    echo "<td>" . $row['unit_pr'] . "</td>";
    echo "<td>" . $row['line_total'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
