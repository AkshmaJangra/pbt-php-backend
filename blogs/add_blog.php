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
$meta_title = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$status = $_POST['status'] ?? '';
$subdes1 = $_POST['subdes1'] ?? '';
$subdes2 = $_POST['subdes2'] ?? '';
$subdes3 = $_POST['subdes3'] ?? '';
$subdes4 = $_POST['subdes4'] ?? '';
$subdes5 = $_POST['subdes5'] ?? '';
$subdes6 = $_POST['subdes6'] ?? '';
$subdes7 = $_POST['subdes7'] ?? '';
$subdes8 = $_POST['subdes8'] ?? '';
$subdes9 = $_POST['subdes9'] ?? '';
$subdes10 = $_POST['subdes10'] ?? '';
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
$imagealt9 = $_POST['imagealt9'] ?? '';
$imagealt10 = $_POST['imagealt10'] ?? '';

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
$sub_image4 = saveImage("sub_image4", $uploadDir);
$sub_image5 = saveImage("sub_image5", $uploadDir);
$sub_image6 = saveImage("sub_image6", $uploadDir);
$sub_image7 = saveImage("sub_image7", $uploadDir);
$sub_image8 = saveImage("sub_image8", $uploadDir);
$sub_image9 = saveImage("sub_image9", $uploadDir);
$sub_image10 = saveImage("sub_image10", $uploadDir);

// Insert
$stmt = $conn->prepare("INSERT INTO blogs (id, title, slug, status, description,meta_title,meta_description, subdes1, subdes2, subdes3,subdes4, subdes5, subdes6,subdes7,subdes8,subdes9,subdes10, image, banner_image, sub_image1, sub_image2, sub_image3, sub_image4, sub_image5, sub_image6,sub_image7,sub_image8,sub_image9,sub_image10,imagealt,banneralt,imagealt1, imagealt2, imagealt3,imagealt4, imagealt5, imagealt6,imagealt7,imagealt8,imagealt9,imagealt10) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmt->bind_param(
    "sssssssssssssssssssssssssssssssssssssss",
    $blogId,
    $title,
    $slug,
    $status,
    $description,
    $meta_title,
    $meta_description,
    $subdes1,
    $subdes2,
    $subdes3,
    $subdes4,
    $subdes5,
    $subdes6,
    $subdes7,
    $subdes8,
    $subdes9,
    $subdes10,
    $image,
    $banner_image,
    $sub_image1,
    $sub_image2,
    $sub_image3,
    $sub_image4,
    $sub_image5,
    $sub_image6,
    $sub_image7,
    $sub_image8,
    $sub_image9,
    $sub_image10,
    $imagealt,
    $banneralt,
     $imagealt1,
    $imagealt2,
    $imagealt3,
    $imagealt4,
    $imagealt5,
    $imagealt6,
    $imagealt7,
    $imagealt8,
    $imagealt9,
    $imagealt10,

);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "New blog added", "id" => $blogId]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
