<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Unique 5-char product ID generator
function generateUniqueProductId($conn) {
    do {
        $id = substr(strtoupper(bin2hex(random_bytes(3))), 0, 5);
        $check = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE id='$id'");
        $row = $check->fetch_assoc();
    } while ($row['cnt'] > 0);
    return $id;
}

// Collect POST data
$title = $_POST['title'] ?? null;
$status = $_POST['status'] ?? null;
$description = $_POST['description'] ?? null;
$price = $_POST['price'] ?? null;
$targetspecies = $_POST['targetspecies'] ?? null;
$category = $_POST['category'] ?? null;
$indications = $_POST['indications'] ?? null;
$composition = $_POST['composition'] ?? null;
$dosages = $_POST['dosages'] ?? null;
$packsize = $_POST['packsize'] ?? null;
$pharmacautionform = $_POST['pharmacautionform'] ?? null;
$slug = $_POST['slug'] ?? null;

$image = null;
$icon_image = null;

// Upload directory (absolute path)
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

if (!empty($_FILES['icon_image']['name'])) {
    $filename = time() . "_" . basename($_FILES['icon_image']['name']);
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['icon_image']['tmp_name'], $targetPath)) {
        $icon_image = "uploads/" . $filename;
    }
}

$id = generateUniqueProductId($conn);

$sql = "INSERT INTO products 
(id, title, status, description, category, price, targetspecies, indications, composition, dosages, packsize, pharmacautionform, slug, image, icon_image) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssssssss",
    $id,
    $title,
    $status,
    $description,
    $category,
    $price,
    $targetspecies,
    $indications,
    $composition,
    $dosages,
    $packsize,
    $pharmacautionform,
    $slug,
    $image,
    $icon_image
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Product added", "id" => $id]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>
