<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Get filters from query string (safe handling)
$category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
$excludeId = isset($_GET['excludeId']) && $_GET['excludeId'] !== '' ? $_GET['excludeId'] : null;

// Base query: fetch only active products
$sql = "SELECT * FROM products WHERE status = 'active'";

// Filter by category
if ($category !== null) {
    $category = $conn->real_escape_string($category);
    $sql .= " AND category = '$category'";
}

// Exclude specific product
if ($excludeId !== null) {
    $excludeId = $conn->real_escape_string($excludeId);
    $sql .= " AND id != '$excludeId'";
}
// Get latest 3 products (by created_at or id if no timestamp exists)
$sql .= " ORDER BY created_at DESC LIMIT 3";
// Debug: Log the final SQL query
error_log("SQL Query: " . $sql);

$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "count" => count($products),
    "data" => $products
]);
?>
