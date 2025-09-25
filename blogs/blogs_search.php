<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Get search keyword safely
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Base query: only active blogs
$sql = "SELECT * FROM blogs WHERE status = 'active'";

// Add search filter if keyword exists
if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $sql .= " AND title LIKE '%$search%'";
}

// Order by latest
$sql .= " ORDER BY created_at DESC";

// Debug (optional)
error_log("Blog Search SQL: " . $sql);

$result = $conn->query($sql);

$blogs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "count" => count($blogs),
    "data" => $blogs
]);

$conn->close();
?>
