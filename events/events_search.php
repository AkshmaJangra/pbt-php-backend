<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Get search keyword safely
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Base query: only active events
$sql = "SELECT id, title, description, slug, image, status, gallery_images 
        FROM events 
        WHERE status = 'active'";

// Add search filter if keyword exists
if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $sql .= " AND title LIKE '%$search%'";
}

// Order by latest (if you have created_at column, otherwise by id)
$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

$events = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "count" => count($events),
    "data" => $events
]);

$conn->close();
?>
