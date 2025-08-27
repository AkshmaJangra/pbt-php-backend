<?php

// include conn.php (go one folder back)
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
$sql = "SELECT id, title, description, profile, review, image, status FROM testimonials";
$result = $conn->query($sql);

$testimonials = [];
while ($row = $result->fetch_assoc()) {
    $testimonials[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $testimonials
]);
?>
