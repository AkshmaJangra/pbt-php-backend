<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Event ID is required"]);
    exit;
}

$res = $conn->query("SELECT * FROM events WHERE id='$id'");
if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Event not found"]);
    exit;
}

if($conn->query("DELETE FROM events WHERE id='$id'")===TRUE){
    echo json_encode(["status" => "success", "message" => "Event deleted successfully"]);

}else{
    echo json_encode(["status" => "error", "message" => "Failed to delete product"]); 
}
?>
