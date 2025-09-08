<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Get filters from query string (safe handling)
$targetspecies = isset($_GET['targetspecies']) && $_GET['targetspecies'] !== '' ? $_GET['targetspecies'] : null;
$category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
$division = isset($_GET['division']) && $_GET['division'] !== '' ? $_GET['division'] : null;

// Base query: fetch only active products
$sql = "SELECT * FROM products WHERE status = 'active'";

// Add filters dynamically
if ($category != null) {
    $category = $conn->real_escape_string($category);
    $sql .= " AND category = '$category'";
}

if ($targetspecies) {
    $targetspecies = $conn->real_escape_string($targetspecies);
    // Use FIND_IN_SET for comma-separated values in targetspecies column
    $sql .= " AND FIND_IN_SET('$targetspecies', targetspecies) > 0";
}

if ($division) {
    $division = $conn->real_escape_string($division);
    $sql .= " AND division = '$division'";
}
$sql .= " ORDER BY created_at DESC";

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