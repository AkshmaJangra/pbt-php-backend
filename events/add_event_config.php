<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}


// Check if record exists (we only want one row)
$check = $conn->query("SELECT * FROM eventconfig LIMIT 1");
$existing = $check->fetch_assoc();


$link1  = $_POST['link1'] ?? ($existing['link1'] ?? "");
$link2  = $_POST['link2'] ?? ($existing['link2'] ?? "");


// Insert or Update
if ($existing) {
    $id = $existing['id'];
    $sql = "UPDATE eventconfig 
            SET link1='$link1', link2='$link2' WHERE id=$id";
} else {
    $sql = "INSERT INTO eventconfig (link1, link2) 
            VALUES ('$link1', '$link2')";
}

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => $existing ? "Event configg updated successfully" : "Event configg created successfully",
        "data" => [
            "link1" => $link1, "link2" => $link2
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$conn->close();
