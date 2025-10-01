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
$showin_home = $_POST['showin_home'] ?? '';
$description = $_POST['description'] ?? '';
$category = $_POST['category'] ?? null;
$division = $_POST['division'] ?? null;
$targetspecies = $_POST['targetspecies'] ?? null;
$indications = $_POST['indications'] ?? '';
$composition = $_POST['composition'] ?? '';
$dosages = $_POST['dosages'] ?? '';
$packsize = $_POST['packsize'] ?? '';
$pharmacautionform = $_POST['pharmacautionform'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$meta_title = $_POST['meta_title'] ?? '';

// ✅ Get current slug
$currentSlug = '';
$getSlug = $conn->prepare("SELECT slug FROM products WHERE id=?");
$getSlug->bind_param("i", $id);
$getSlug->execute();
$getSlug->bind_result($currentSlug);
$getSlug->fetch();
$getSlug->close();

// ✅ Check if slug is being changed
if (!empty($slug) && $slug !== $currentSlug) {
    $checkSlug = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
    $checkSlug->bind_param("si", $slug, $id);
    $checkSlug->execute();
    $result = $checkSlug->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Slug already exists for another product"]);
        exit;
    }
    $checkSlug->close();
}

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

// ✅ Handle other_images upload (multiple files)
$other_images = null;
if (!empty($_FILES['other_images']['name'][0])) {
    $uploadedImages = [];
    $fileCount = count($_FILES['other_images']['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        if (!empty($_FILES['other_images']['name'][$i])) {
            $fileName = "uploads/" . time() . "_" . $i . "_" . basename($_FILES['other_images']['name'][$i]);
            $targetPath = __DIR__ . "/../" . $fileName;
            if (move_uploaded_file($_FILES['other_images']['tmp_name'][$i], $targetPath)) {
                $uploadedImages[] = $fileName;
            }
        }
    }
    if (!empty($uploadedImages)) {
        $other_images = json_encode($uploadedImages);
    }
}

// ✅ Handle pack_insert upload (PDF)
$pack_insert = null;
if (!empty($_FILES['pack_insert']['name'])) {
    $pack_insert = "uploads/" . time() . "_" . basename($_FILES['pack_insert']['name']);
    $targetPath = __DIR__ . "/../" . $pack_insert;
    if (!move_uploaded_file($_FILES['pack_insert']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload pack insert"]);
        exit;
    }
}

// ✅ Handle technical_enquiry upload (PDF)
$technical_enquiry = null;
if (!empty($_FILES['technical_enquiry']['name'])) {
    $technical_enquiry = "uploads/" . time() . "_" . basename($_FILES['technical_enquiry']['name']);
    $targetPath = __DIR__ . "/../" . $technical_enquiry;
    if (!move_uploaded_file($_FILES['technical_enquiry']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload technical enquiry"]);
        exit;
    }
}

// ✅ Build query dynamically
$sql = "UPDATE products SET 
    title=?, status=?, showin_home=?, description=?, category=?, division=?,
    targetspecies=?, indications=?, composition=?, dosages=?, 
    packsize=?, pharmacautionform=?, meta_description=?, meta_title=?";

$params = [
    $title, $status, $showin_home, $description, $category, $division,
    $targetspecies, $indications, $composition, $dosages,
    $packsize, $pharmacautionform, $meta_description, $meta_title
];

// ✅ Only update slug if it was changed
if (!empty($slug) && $slug !== $currentSlug) {
    $sql .= ", slug=?";
    $params[] = $slug;
}

// Add optional fields only if they're being updated
if ($image) {
    $sql .= ", image=?";
    $params[] = $image;
}
if ($icon_image) {
    $sql .= ", icon_image=?";
    $params[] = $icon_image;
}
if ($other_images) {
    $sql .= ", other_images=?";
    $params[] = $other_images;
}
if ($pack_insert) {
    $sql .= ", pack_insert=?";
    $params[] = $pack_insert;
}
if ($technical_enquiry) {
    $sql .= ", technical_enquiry=?";
    $params[] = $technical_enquiry;
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
    echo json_encode(["status" => "error", "message" => "SQL prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);

// ✅ Execute
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Product updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update product: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
