<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Fetch only active events
$sql = "SELECT id, title, description, slug, image, status, gallery_images 
        FROM events 
        WHERE status = 'active'";

$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode(["status" => "success", "data" => $events]);

$conn->close();
?>
