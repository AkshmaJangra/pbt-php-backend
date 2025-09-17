<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$sql = "SELECT * FROM eventconfig";

$result = $conn->query($sql);

$eventconfigs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $eventconfigs[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "data" => $eventconfigs
]);
?>
