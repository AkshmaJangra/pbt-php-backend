<?php

// include conn.php (go one folder back)
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
$sql = "SELECT id, name, email, mobile,message FROM contactus";
$result = $conn->query($sql);

$contactus = [];
while ($row = $result->fetch_assoc()) {
    $contactus[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $contactus
]);
?>
