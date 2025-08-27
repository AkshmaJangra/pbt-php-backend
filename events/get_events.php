<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$sql = "SELECT id, title, description, slug, image, status FROM events";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode(["status" => "success", "data" => $events]);
$conn->close();
?>
