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
$gallery_images = [];
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
// ✅ Handle multiple other images
if (!empty($_FILES['gallery_images']['name'][0])) {
    foreach ($_FILES['gallery_images']['name'] as $key => $val) {
        if (!empty($val)) {
            $filename = time() . "_" . basename($val);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], $targetPath)) {
                $gallery_images[] = "uploads/" . $filename;
            }
        }
    }
}
// Generate unique event ID
$id = generateUniqueEventId($conn);
$gallery_images_json = json_encode($gallery_images);

$sql="INSERT INTO events (id, title, description, status, slug, image,gallery_images)VALUES (?, ?, ?, ?, ?, ?,?)";
// Insert into DB
$stmt =$conn->prepare($sql);
$stmt->bind_param("sssssss", $id, $title, $description, $status, $slug, $image,$gallery_images_json);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Event added successfully", "id" => $id]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
