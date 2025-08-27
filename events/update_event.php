<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");


if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Check if ID is sent
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}

$id = intval($_POST['id']);
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$status = $_POST['status'] ?? '';
$slug = $_POST['slug'] ?? '';
// Directory for uploads (one level up from products/)
$uploadDir = __DIR__ . "/../uploads/";
// Ensure uploads folder exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$image = null;
if (!empty($_FILES['image']['name'])) {
    $image = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
    $targetPath = __DIR__ . "/../" . $image;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
        exit;
    }
}


$sql = "UPDATE events SET title=?,slug=?, description=?, status=?";
$params = [
    $title, $slug, $description, $status
];
if ($image) {
    $sql .= ", image=?";
    $params[] = $image;

}

$sql .= " WHERE id=?";
$params[] = $id;
// ✅ Generate types string dynamically
$types = "";
foreach ($params as $p) {
    if (is_int($p)) {
        $types .= "i";
    } elseif (is_float($p) || is_double($p)) {
        $types .= "d";
    } else {
        $types .= "s";
    }
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "SQL prepare failed"]);
    exit;
}
$stmt->bind_param($types, ...$params);
// ✅ Execute
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Product updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update product"]);
}



$stmt->close();
$conn->close();
?>
