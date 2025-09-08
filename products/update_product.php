<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");


// ✅ Check DB connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// ✅ Check if ID is sent
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}

$id = intval($_POST['id']);
$title = $_POST['title'] ?? '';
$slug = $_POST['slug'] ?? '';
$status = $_POST['status'] ?? '';
$description = $_POST['description'] ?? '';
$category = $_POST['category'] ?? null; // nullable
$division = $_POST['division'] ?? null; // nullable
$targetspecies = $_POST['targetspecies'] ?? null;
$indications = $_POST['indications'] ?? '';
$composition = $_POST['composition'] ?? '';
$dosages = $_POST['dosages'] ?? '';
$packsize = $_POST['packsize'] ?? '';
$pharmacautionform = $_POST['pharmacautionform'] ?? '';

// ✅ Directory for uploads
$uploadDir = __DIR__ . "/../uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Handle image upload
$image = null;
if (!empty($_FILES['image']['name'])) {
    $image = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
    $targetPath = __DIR__ . "/../" . $image;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
        exit;
    }
}

// ✅ Handle icon_image upload
$icon_image = null;
if (!empty($_FILES['icon_image']['name'])) {
    $icon_image = "uploads/" . time() . "_" . basename($_FILES['icon_image']['name']);
    $targetPath = __DIR__ . "/../" . $icon_image;
    if (!move_uploaded_file($_FILES['icon_image']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload icon image"]);
        exit;
    }
}

// ✅ Build query dynamically
$sql = "UPDATE products SET 
    title=?, slug=?, status=?, description=?, category=?,division=?,
    targetspecies=?, indications=?, composition=?, dosages=?, 
    packsize=?, pharmacautionform=?";

$params = [
    $title, $slug, $status, $description, $category,$division,
    $targetspecies, $indications, $composition, $dosages,
    $packsize, $pharmacautionform
];

// Add optional fields
if ($image) {
    $sql .= ", image=?";
    $params[] = $image;
}
if ($icon_image) {
    $sql .= ", icon_image=?";
    $params[] = $icon_image;
}

// Add WHERE clause
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

// ✅ Prepare and bind
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
