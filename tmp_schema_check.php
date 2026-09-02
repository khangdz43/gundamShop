<?php
$conn = new mysqli('localhost', 'root', '', 'gundam_store');
if ($conn->connect_error) {
    die('DB connect failed: ' . $conn->connect_error);
}
$tables = ['products','categories','users','coupons','notifications','notification_users','orders','order_items'];
foreach ($tables as $t) {
    echo "Table: $t\n";
    $res = $conn->query("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='gundam_store' AND TABLE_NAME='$t' ORDER BY ORDINAL_POSITION");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo $row['COLUMN_NAME'] . ' ' . $row['COLUMN_TYPE'] . ' ' . $row['IS_NULLABLE'] . ' ' . $row['COLUMN_KEY'] . "\n";
        }
    } else {
        echo 'missing table\n';
    }
    echo "\n";
}
$conn->close();
