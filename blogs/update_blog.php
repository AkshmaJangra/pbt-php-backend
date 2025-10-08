<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// ✅ Check DB connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}
// Safely get ID
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}


$id = intval($_POST['id']);
$title = $_POST['title'] ?? '';
$slug = $_POST['slug'] ?? '';
$status = $_POST['status'] ?? '';
$description = $_POST['description'] ?? '';
$meta_title = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$subdes1 = $_POST['subdes1'] ?? '';
$subdes2 = $_POST['subdes2'] ?? '';
$subdes3 = $_POST['subdes3'] ?? '';
$subdes4 = $_POST['subdes4'] ?? '';
$subdes5 = $_POST['subdes5'] ?? '';
$subdes6 = $_POST['subdes6'] ?? '';
$subdes7 = $_POST['subdes7'] ?? '';
$subdes8 = $_POST['subdes8'] ?? '';
$imagealt=$_POST['imagealt']??'';
$banneralt=$_POST['banneralt']??'';
$imagealt1 = $_POST['imagealt1'] ?? '';
$imagealt2 = $_POST['imagealt2'] ?? '';
$imagealt3 = $_POST['imagealt3'] ?? '';
$imagealt4 = $_POST['imagealt4'] ?? '';
$imagealt5 = $_POST['imagealt5'] ?? '';
$imagealt6 = $_POST['imagealt6'] ?? '';
$imagealt7 = $_POST['imagealt7'] ?? '';
$imagealt8 = $_POST['imagealt8'] ?? '';

// ✅ Directory for uploads
$uploadDir = __DIR__ . "/../uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Function to handle file uploads
function uploadFile($fieldName) {
    if (!empty($_FILES[$fieldName]['name'])) {
        $filePath = "uploads/" . time() . "_" . basename($_FILES[$fieldName]['name']);
        $targetPath = __DIR__ . "/../" . $filePath;
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
            return $filePath;
        }
    }
    return null;
}

// ✅ Upload files
$image = uploadFile("image");
$sub_image1 = uploadFile("sub_image1");
$sub_image2 = uploadFile("sub_image2");
$sub_image3 = uploadFile("sub_image3");
$sub_image4 = uploadFile("sub_image4");
$sub_image5 = uploadFile("sub_image5");
$sub_image6 = uploadFile("sub_image6");
$sub_image7 = uploadFile("sub_image7");
$sub_image8 = uploadFile("sub_image8");
$banner_image = uploadFile("banner_image");

// ✅ Build query dynamically
$sql = "UPDATE blogs SET 
    title=?, slug=?, status=?, description=?,meta_title=?,meta_description=?, subdes1=?, subdes2=?, subdes3=?, subdes4=?, subdes5=?, subdes6=?,subdes7=?,subdes8=?,imagealt=?,banneralt=?,imagealt1=?, imagealt2=?, imagealt3=?,imagealt4=?, imagealt5=?, imagealt6=?,imagealt7=?,imagealt8=?";

$params = [$title, $slug, $status, $description,$meta_title,$meta_description, $subdes1, $subdes2, $subdes3,$subdes4, $subdes5, $subdes6,$subdes7,$subdes8,$imagealt,$banneralt,$imagealt1, $imagealt2, $imagealt3,$imagealt4, $imagealt5, $imagealt6,$imagealt7,$imagealt8];

// Add optional fields
if ($image) {
    $sql .= ", image=?";
    $params[] = $image;
}
if ($sub_image1) {
    $sql .= ", sub_image1=?";
    $params[] = $sub_image1;
}
if ($sub_image2) {
    $sql .= ", sub_image2=?";
    $params[] = $sub_image2;
}
if ($sub_image3) {
    $sql .= ", sub_image3=?";
    $params[] = $sub_image3;
}
if ($sub_image4) {
    $sql .= ", sub_image4=?";
    $params[] = $sub_image4;
}
if ($sub_image5) {
    $sql .= ", sub_image5=?";
    $params[] = $sub_image5;
}
if ($sub_image6) {
    $sql .= ", sub_image6=?";
    $params[] = $sub_image6;
}
if ($sub_image7) {
    $sql .= ", sub_image7=?";
    $params[] = $sub_image7;
}
if ($sub_image8) {
    $sql .= ", sub_image8=?";
    $params[] = $sub_image8;
}
if ($banner_image) {
    $sql .= ", banner_image=?";
    $params[] = $banner_image;
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
    echo json_encode(["status" => "success", "message" => "Blog updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update blog"]);
}

$stmt->close();
$conn->close();
?>
