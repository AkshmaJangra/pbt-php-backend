<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");


// Base query: fetch only active blogs
$sql = "SELECT * FROM blogs WHERE status = 'active'";

$sql .= " ORDER BY created_at DESC";

// Debug: Log the final SQL query
error_log("SQL Query: " . $sql);

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

?>