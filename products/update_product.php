<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "Product ID is required"]);
    exit;
}

$id = $_POST['id'];

// Fetch existing product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current) {
    echo json_encode(["status" => "error", "message" => "Product not found"]);
    exit;
}

// Build dynamic query
$fields = [];
$params = [];
$types = "";

// Loop through POST data
foreach ($_POST as $key => $value) {
    if ($key === 'id') continue;

    // Skip unchanged or empty values (if needed)
    $oldValue = isset($current[$key]) ? trim((string)$current[$key]) : null;
    $newValue = trim((string)$value);

    if ($newValue === $oldValue) continue;

    // Special case: slug
    if ($key === 'slug') {
        $normalizedCurrentSlug = strtolower(trim($current['slug']));
        $normalizedNewSlug = strtolower(trim($value));
        if ($normalizedCurrentSlug === $normalizedNewSlug) continue;
    }

    $fields[] = "$key=?";
    $params[] = $value;
    $types .= "s";
}

// ✅ Handle image uploads (if any)
if (!empty($_FILES['other_images']['name'][0])) {
    $uploadDir = __DIR__ . "/../../uploads/";
    $fileNames = [];

    foreach ($_FILES['other_images']['name'] as $index => $fileName) {
        $targetFile = $uploadDir . basename($fileName);
        if (move_uploaded_file($_FILES['other_images']['tmp_name'][$index], $targetFile)) {
            $fileNames[] = $fileName;
        }
    }

    if (!empty($fileNames)) {
        $fields[] = "other_images=?";
        $params[] = implode(",", $fileNames);
        $types .= "s";
    }
}

// Nothing to update
if (empty($fields)) {
    echo json_encode(["status" => "success", "message" => "No changes detected"]);
    exit;
}

// Build final query
$sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id=?";
$params[] = $id;
$types .= "s";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

try {
    $stmt->execute();
    echo json_encode(["status" => "success", "message" => "Product updated successfully"]);
} catch (mysqli_sql_exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
