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

// Fetch current product
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("s", $id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current) {
    echo json_encode(["status" => "error", "message" => "Product not found"]);
    exit;
}

// Upload directory
$uploadDir = __DIR__ . "/../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Fields to update
$fields = [];
$params = [];
$types = "";

// 1️⃣ Update normal fields
$updatableFields = [
    'title','status','showin_home','description','category','division','targetspecies',
    'indications','composition','dosages','packsize','pharmacautionform','slug',
    'meta_description','meta_title'
];

foreach ($updatableFields as $key) {
    if (isset($_POST[$key])) {
        $newValue = trim($_POST[$key]);
        $oldValue = isset($current[$key]) ? trim((string)$current[$key]) : '';
        // Slug is case-insensitive
        if ($key === 'slug' && strtolower($newValue) === strtolower($oldValue)) continue;
        if ($key !== 'slug' && $newValue === $oldValue) continue;

        $fields[] = "$key=?";
        $params[] = $newValue;
        $types .= "s";
    }
}

// 2️⃣ Handle single file uploads
$singleFiles = ['image','icon_image','pack_insert','technical_enquiry'];
foreach ($singleFiles as $fileKey) {
    if (!empty($_FILES[$fileKey]['name'])) {
        $filename = time() . "_" . basename($_FILES[$fileKey]['name']);
        $targetPath = $uploadDir . $filename;

        // PDFs only for pack_insert & technical_enquiry
        if (in_array($fileKey, ['pack_insert','technical_enquiry'])) {
            $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
            if ($ext !== "pdf") continue;
        }

        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
            $fields[] = "$fileKey=?";
            $params[] = "uploads/" . $filename;
            $types .= "s";
        }
    }
}

// 3️⃣ Handle multiple other_images
$other_images = json_decode($current['other_images'] ?? '[]', true);

if (!empty($_FILES['other_images']['name'])) {
    $names = is_array($_FILES['other_images']['name']) ? $_FILES['other_images']['name'] : [$_FILES['other_images']['name']];
    $tmp_names = is_array($_FILES['other_images']['tmp_name']) ? $_FILES['other_images']['tmp_name'] : [$_FILES['other_images']['tmp_name']];

    foreach ($names as $i => $name) {
        if (!empty($name)) {
            $filename = time() . "_" . basename($name);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($tmp_names[$i], $targetPath)) {
                $other_images[] = "uploads/" . $filename;
            }
        }
    }

    if (!empty($other_images)) {
        $fields[] = "other_images=?";
        $params[] = json_encode($other_images);
        $types .= "s";
    }
}

// 4️⃣ Nothing to update
if (empty($fields)) {
    echo json_encode(["status" => "success", "message" => "No changes detected"]);
    exit;
}

// 5️⃣ Build and execute UPDATE query
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
