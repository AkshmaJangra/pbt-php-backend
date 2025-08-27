<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
include "../utils/helpers.php";

$title = $_POST['title'] ?? null;
$description = $_POST['description'] ?? null;
$status = $_POST['status'] ?? null;
$slug = $_POST['slug'] ?? null;

if (!$title || !$description || !$status || !$slug) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Handle image upload
$image = null;
$uploadDir = __DIR__ . "/../uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // auto-create if missing
}
// handle file uploads
if (!empty($_FILES['image']['name'])) {
    $filename = time() . "_" . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image = "uploads/" . $filename; // store relative path in DB
    }
}

// Generate unique event ID
$id = generateUniqueEventId($conn);
$sql="INSERT INTO events (id, title, description, status, slug, image)VALUES (?, ?, ?, ?, ?, ?)";
// Insert into DB
$stmt =$conn->prepare($sql);
$stmt->bind_param("ssssss", $id, $title, $description, $status, $slug, $image);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Event added successfully", "id" => $id]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
