<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Get filters from query string (safe handling)
$targetspecies = isset($_GET['targetspecies']) && $_GET['targetspecies'] !== '' ? $_GET['targetspecies'] : null;
$category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
$division = isset($_GET['division']) && $_GET['division'] !== '' ? $_GET['division'] : null;
$domains = isset($_GET['domains']) && $_GET['domains'] !== '' ? $_GET['domains'] : null;
$showin_home = isset($_GET['showin_home']) && $_GET['showin_home'] !== '' ? $_GET['showin_home'] : null;
$search = isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : null;

// ✅ Pagination defaults
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? intval($_GET['limit']) : 12;
$offset = ($page - 1) * $limit;

// Base query
$sqlBase = "FROM products WHERE status = 'active'";

// Add filters dynamically
if ($category != null) {
    $category = $conn->real_escape_string($category);
    $sqlBase .= " AND category = '$category'";
}

if ($targetspecies && $targetspecies !== 'All') {
    $targetspecies = $conn->real_escape_string($targetspecies);
    $sqlBase .= " AND FIND_IN_SET('$targetspecies', targetspecies) > 0";
}
if ($domains && $domains !== 'All') {
    $domains = $conn->real_escape_string($domains);
    $sqlBase .= " AND FIND_IN_SET('$domains', domains) > 0";
}

if ($division) {
    $division = $conn->real_escape_string($division);
    $sqlBase .= " AND division = '$division'";
}

if ($search) {
    $search = $conn->real_escape_string($search);
    $sqlBase .= " AND (title LIKE '%$search%' OR category LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($showin_home) {
    $sqlBase = "FROM products WHERE showin_home = 'active'";
}

// ✅ Count total records
$countQuery = "SELECT COUNT(*) AS total " . $sqlBase;
$countResult = $conn->query($countQuery);
$totalRecords = ($countResult && $countResult->num_rows > 0) ? $countResult->fetch_assoc()['total'] : 0;

// ✅ Fetch paginated data
$sql = "SELECT * " . $sqlBase . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
error_log("SQL Query: " . $sql);

$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// ✅ Calculate pagination meta
$totalPages = ceil($totalRecords / $limit);

echo json_encode([
    "status" => "success",
    "count" => count($products),
    "total_records" => $totalRecords,
    "total_pages" => $totalPages,
    "current_page" => $page,
    "limit" => $limit,
    "data" => $products
]);
?>
