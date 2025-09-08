<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Blog ID required"]);
    exit;
}

$res = $conn->query("SELECT * FROM blogs WHERE id='$id'");

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Blog not found"]);
    exit;
}

$conn->query("DELETE FROM blogs WHERE id='$id'");
echo json_encode(["status" => "success", "message" => "Blog deleted"]);

?>
