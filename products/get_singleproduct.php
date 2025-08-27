<?php
include_once(__DIR__ . "/../cors.php");

// include conn.php (go one folder back)
include_once(__DIR__ . "/../conn.php");

// Get slug from query parameter (?slug=some-slug)
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    echo json_encode([
        "status" => "error",
        "message" => "Slug is required"
    ]);
    exit;
}

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM products WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "data" => $product
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Product not found"
    ]);
}

$stmt->close();
$conn->close();
?>
