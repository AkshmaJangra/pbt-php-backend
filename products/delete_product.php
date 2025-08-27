<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$id = $_GET['id'] ?? null;  // ✅ use GET instead of POST
if (!$id) {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}

// check if product exists
$check = $conn->query("SELECT * FROM products WHERE id='$id'");
if ($check->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "Product not found"]);
    exit;
}

// delete product
if ($conn->query("DELETE FROM products WHERE id='$id'") === TRUE) {
    echo json_encode(["status" => "success", "message" => "Product deleted"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete product"]);
}
?>
