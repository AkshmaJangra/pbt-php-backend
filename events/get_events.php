<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");


$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT id, title, description, slug, image, status,gallery_images FROM events";
if ($status === 'active') {
    $sql .= " WHERE status = 'active'";
}

// Add search filter if keyword exists
if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $sql .= " AND title LIKE '%$search%'";
}
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode(["status" => "success", "data" => $events]);
$conn->close();
?>
