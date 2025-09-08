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
$targetspecies = $_POST['targetspecies'] ?? 'All';
$category = $_POST['category'] ?? null;
$division = $_POST['division'] ?? null;
$indications = $_POST['indications'] ?? null;
$composition = $_POST['composition'] ?? null;
$dosages = $_POST['dosages'] ?? null;
$packsize = $_POST['packsize'] ?? null;
$pharmacautionform = $_POST['pharmacautionform'] ?? null;
$slug = $_POST['slug'] ?? null;

$image = null;
$other_images = [];
$technical_enquiry = null;
$pack_insert = null;
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
// ✅ Handle multiple other images
if (!empty($_FILES['other_images']['name'][0])) {
    foreach ($_FILES['other_images']['name'] as $key => $val) {
        if (!empty($val)) {
            $filename = time() . "_" . basename($val);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['other_images']['tmp_name'][$key], $targetPath)) {
                $other_images[] = "uploads/" . $filename;
            }
        }
    }
}
// ✅ Handle PDFs
if (!empty($_FILES['pack_insert']['name'])) {
    if (strtolower(pathinfo($_FILES['pack_insert']['name'], PATHINFO_EXTENSION)) === "pdf") {
        $filename = time() . "_" . basename($_FILES['pack_insert']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['pack_insert']['tmp_name'], $targetPath)) {
            $pack_insert = "uploads/" . $filename;
        }
    }
}
if (!empty($_FILES['technical_enquiry']['name'])) {
    if (strtolower(pathinfo($_FILES['technical_enquiry']['name'], PATHINFO_EXTENSION)) === "pdf") {
        $filename = time() . "_" . basename($_FILES['technical_enquiry']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['technical_enquiry']['tmp_name'], $targetPath)) {
            $technical_enquiry = "uploads/" . $filename;
        }
    }
}

$id = generateUniqueProductId($conn);
// Store other_images as JSON (better than comma separated)
$other_images_json = json_encode($other_images);

$sql = "INSERT INTO products 
(id, title, status, description, category, division, targetspecies, indications, composition, dosages, packsize, pharmacautionform, slug, image, icon_image, other_images, pack_insert, technical_enquiry) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
   "ssssssssssssssssss", // 18 fields
    $id,
    $title,
    $status,
    $description,
    $category,
    $division,
    $targetspecies,
    $indications,
    $composition,
    $dosages,
    $packsize,
    $pharmacautionform,
    $slug,
    $image,
    $icon_image,
    $other_images_json,
    $pack_insert,
    $technical_enquiry
);


if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Product added", "id" => $id]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>
