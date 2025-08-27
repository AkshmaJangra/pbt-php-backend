<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$sql = "SELECT id, title,slug, description,subdes1,subdes2,subdes3 ,banner_image, image, status, sub_image1, sub_image2, sub_image3 FROM blogs";
$result = $conn->query($sql);

$blogs = [];
while ($row = $result->fetch_assoc()) {
    $blogs[] = $row;
}

echo json_encode(["status" => "success", "data" => $blogs]);
?>
