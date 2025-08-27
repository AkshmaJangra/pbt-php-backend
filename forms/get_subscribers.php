<?php

// include conn.php (go one folder back)
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
$sql = "SELECT id, name, email FROM newsletter";
$result = $conn->query($sql);

$subscribers = [];
while ($row = $result->fetch_assoc()) {
    $subscribers[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $subscribers
]);
?>
