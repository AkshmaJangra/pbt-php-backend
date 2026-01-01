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
    'title','status','showin_home','description','category','division','targetspecies','domains',
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
            // Delete old file if exists
            if (!empty($current[$fileKey]) && file_exists(__DIR__ . "/../" . $current[$fileKey])) {
                unlink(__DIR__ . "/../" . $current[$fileKey]);
            }
            
            $fields[] = "$fileKey=?";
            $params[] = "uploads/" . $filename;
            $types .= "s";
        }
    }
}

// 3️⃣ Handle multiple other_images with delete functionality
$shouldUpdateOtherImages = false;
$other_images = [];

// Get current images from database
$current_images = json_decode($current['other_images'] ?? '[]', true);
if (!is_array($current_images)) {
    $current_images = [];
}

// Check if we have existing_other_images (images to keep)
if (isset($_POST['existing_other_images']) && !empty($_POST['existing_other_images'])) {
    $existing = json_decode($_POST['existing_other_images'], true);
    if (is_array($existing)) {
        $other_images = $existing;
        $shouldUpdateOtherImages = true;
    }
} else {
    // If existing_other_images is empty or not set, start with empty array
    // This means user wants to remove all existing images
    if (isset($_POST['existing_other_images'])) {
        $other_images = [];
        $shouldUpdateOtherImages = true;
    } else {
        // If not sent at all, keep current ones
        $other_images = $current_images;
    }
}

// Delete images that are in current but not in existing (images to delete)
if (isset($_POST['delete_other_images']) && !empty($_POST['delete_other_images'])) {
    $toDelete = json_decode($_POST['delete_other_images'], true);
    if (is_array($toDelete)) {
        foreach ($toDelete as $imgPath) {
            // Clean the path - remove backslashes
            $cleanPath = str_replace("\\", "", $imgPath);
            $fullPath = __DIR__ . "/../" . $cleanPath;
            
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        $shouldUpdateOtherImages = true;
    }
}

// Add new uploaded images
if (!empty($_FILES['other_images']['name'])) {
    $names = $_FILES['other_images']['name'];
    $tmp_names = $_FILES['other_images']['tmp_name'];
    $errors = $_FILES['other_images']['error'];
    
    // Handle both single and multiple file uploads
    if (!is_array($names)) {
        $names = [$names];
        $tmp_names = [$tmp_names];
        $errors = [$errors];
    }

    foreach ($names as $i => $name) {
        if (!empty($name) && $errors[$i] === UPLOAD_ERR_OK && !empty($tmp_names[$i])) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $filename = time() . "_" . uniqid() . "_" . basename($name);
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($tmp_names[$i], $targetPath)) {
                    $other_images[] = "uploads/" . $filename;
                    $shouldUpdateOtherImages = true;
                }
            }
        }
    }
}

// Update other_images field if there were any changes
if ($shouldUpdateOtherImages) {
    $fields[] = "other_images=?";
    $params[] = json_encode($other_images);
    $types .= "s";
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