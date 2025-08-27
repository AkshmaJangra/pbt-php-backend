<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Check if ID is sent
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}

$id = $_POST['id'] ?? '';
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$profile = $_POST['profile'] ?? '';
$review = $_POST['review'] ?? '';
$status = $_POST['status'] ?? 'inactive';


$uploadDir = __DIR__ . "/../uploads/";

// Ensure uploads folder exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// check if testimonial exists
$stmt = $conn->prepare("SELECT * FROM testimonials WHERE id=?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Testimonial not found"]);
    exit;
}
$testimonial = $result->fetch_assoc();

// handle image upload (if provided)
$imagePath = $testimonial['image'];
// Handle image upload
$image = null;
if (!empty($_FILES['image']['name'])) {
    $image = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
    $targetPath = __DIR__ . "/../" . $image;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
        exit;
    }
}

// update testimonial
$sql = "UPDATE testimonials SET title=?, description=?, profile=?, review=?, status=?";

if ($image) {
    $sql .= ", image=?";
}
$sql .= " WHERE id=?";
$stmt = $conn->prepare($sql);

if ($image) {
    $stmt->bind_param("sssssss",  $title, $description, $profile, $review, $status, $image, $id);
}  else {
    $stmt->bind_param("ssssss", $title, $description, $profile, $review, $status,  $id);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Testimonial updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Update failed"]);
}


$stmt->close();
$conn->close();
?>
