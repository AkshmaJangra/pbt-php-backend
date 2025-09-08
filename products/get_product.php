<?php
include_once(__DIR__ . "/../cors.php");

// include conn.php (go one folder back)
include_once(__DIR__ . "/../conn.php");

$sql = "SELECT * FROM products";
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "data" => $products
]);
?>
