<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Create uploads dir if not exists
$uploadDir = "../uploads/blogs/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$title = $_POST['title'] ?? '';
$slug = $_POST['slug'] ?? '';
$description = $_POST['description'] ?? '';
$status = $_POST['status'] ?? '';
$subdes1 = $_POST['subdes1'] ?? '';
$subdes2 = $_POST['subdes2'] ?? '';
$subdes3 = $_POST['subdes3'] ?? '';
// Generate unique blog ID (5 chars)
function generateUniqueBlogId($conn) {
    do {
        $id = strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));
        $res = $conn->query("SELECT COUNT(*) AS count FROM blogs WHERE id = '$id'");
        $row = $res->fetch_assoc();
    } while ($row['count'] > 0);
    return $id;
}

$blogId = generateUniqueBlogId($conn);

// Handle images
function saveImage($fileKey, $uploadDir) {
    if (!empty($_FILES[$fileKey]['name'])) {
        $filename = uniqid() . "_" . basename($_FILES[$fileKey]['name']);
        $targetPath = $uploadDir . $filename;
        move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath);
        return "uploads/blogs/" . $filename;
    }
    return null;
}

$image = saveImage("image", $uploadDir);
$banner_image = saveImage("banner_image", $uploadDir);
$sub_image1 = saveImage("sub_image1", $uploadDir);
$sub_image2 = saveImage("sub_image2", $uploadDir);
$sub_image3 = saveImage("sub_image3", $uploadDir);

// Insert
$stmt = $conn->prepare("INSERT INTO blogs (id, title, slug, status, description, subdes1, subdes2, subdes3, image, banner_image, sub_image1, sub_image2, sub_image3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sssssssssssss",
    $blogId,
    $title,
    $slug,
    $status,
    $description,
    $subdes1,
    $subdes2,
    $subdes3,
    $image,
    $banner_image,
    $sub_image1,
    $sub_image2,
    $sub_image3
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "New blog added", "id" => $blogId]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
