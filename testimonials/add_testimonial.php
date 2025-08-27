<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
// Unique 5-char testimonial ID generator
function generateUniqueTestimonialId($conn) {
    do {
        $id = substr(strtoupper(bin2hex(random_bytes(3))), 0, 5);
        $check = $conn->query("SELECT COUNT(*) as cnt FROM testimonials WHERE id='$id'");
        $row = $check->fetch_assoc();
    } while ($row['cnt'] > 0);
    return $id;
}

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$profile = $_POST['profile'] ?? '';
$review = $_POST['review'] ?? '';
$status = $_POST['status'] ?? 'inactive';

// handle image upload
$image = null;
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

// generate unique ID
$id = generateUniqueTestimonialId($conn);

// insert testimonial
$sql ="INSERT INTO testimonials (id, title, description, profile, review, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("sssssss", $id, $title, $description, $profile, $review, $status, $image);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Testimonial added successfully",
        "data" => [
            "id" => $id,
            "title" => $title,
            "description" => $description,
            "profile" => $profile,
            "review" => $review,
            "status" => $status,
            "image" => $image
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add testimonial"]);
}
?>
