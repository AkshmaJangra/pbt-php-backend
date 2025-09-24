<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$sql = "SELECT id, title,slug, description,subdes1,subdes2,subdes3 ,subdes4,subdes5,subdes6 ,subdes7,subdes8,banner_image, image, status, sub_image1, sub_image2, sub_image3,sub_image4, sub_image5, sub_image6,sub_image7,sub_image8 FROM blogs";
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

$blogs = [];
while ($row = $result->fetch_assoc()) {
    $blogs[] = $row;
}

echo json_encode(["status" => "success", "data" => $blogs]);
?>
