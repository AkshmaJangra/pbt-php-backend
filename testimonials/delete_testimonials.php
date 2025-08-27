<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}

$stmt = $conn->query("SELECT * FROM testimonials WHERE id='$id'");

if ($stmt->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "Product not found"]);
    exit;
}


if( $conn->query("DELETE FROM testimonials WHERE id='$id'")===TRUE){
 echo json_encode(["status" => "success", "message" => "Testimonial deleted successfully"]);
}else{
    echo json_encode(["status" => "error", "message" => "Failed to delete testimonials"]);
}

?>
