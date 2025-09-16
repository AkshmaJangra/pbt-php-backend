<?php
include_once(__DIR__ . "/../cors.php");

// include conn.php (go one folder back)
include_once(__DIR__ . "/../conn.php");

$sql = "SELECT * FROM jobpostings";

$result = $conn->query($sql);

$jobs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "data" => $jobs
]);
?>
