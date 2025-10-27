<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Check if ID is sent
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}

$id = intval($_POST['id']);
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$status = $_POST['status'] ?? '';
$slug = $_POST['slug'] ?? '';

// Directory for uploads
$uploadDir = __DIR__ . "/../uploads/";

// Ensure uploads folder exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle main image upload
$image = null;
if (!empty($_FILES['image']['name'])) {
    $image = "uploads/" . time() . "_" . basename($_FILES['image']['name']);
    $targetPath = __DIR__ . "/../" . $image;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
        exit;
    }
}

// Handle gallery images
$finalGalleryImages = [];

// 1. Get existing gallery images (if any)
if (isset($_POST['existing_gallery_images']) && !empty($_POST['existing_gallery_images'])) {
    $existingImages = json_decode($_POST['existing_gallery_images'], true);
    if (is_array($existingImages)) {
        $finalGalleryImages = $existingImages;
    }
}

// 2. Delete images that user wants to remove
if (isset($_POST['images_to_delete']) && !empty($_POST['images_to_delete'])) {
    $imagesToDelete = json_decode($_POST['images_to_delete'], true);
    if (is_array($imagesToDelete)) {
        foreach ($imagesToDelete as $imagePath) {
            // Remove from final array
            $finalGalleryImages = array_filter($finalGalleryImages, function($img) use ($imagePath) {
                return $img !== $imagePath;
            });
            
            // Delete physical file
            $fullPath = __DIR__ . "/../" . str_replace("\\", "/", $imagePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        // Re-index array after filtering
        $finalGalleryImages = array_values($finalGalleryImages);
    }
}

// 3. Add new uploaded images
if (!empty($_FILES['gallery_images']['name'][0])) {
    $fileCount = count($_FILES['gallery_images']['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        if (!empty($_FILES['gallery_images']['name'][$i])) {
            $fileName = "uploads/" . time() . "_" . $i . "_" . basename($_FILES['gallery_images']['name'][$i]);
            $targetPath = __DIR__ . "/../" . $fileName;
            
            if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $targetPath)) {
                $finalGalleryImages[] = $fileName;
            }
        }
    }
}

// Prepare SQL query
$sql = "UPDATE events SET title=?, slug=?, description=?, status=?";
$params = [$title, $slug, $description, $status];

// Add main image to update if provided
if ($image) {
    $sql .= ", image=?";
    $params[] = $image;
}

// Always update gallery_images (even if empty, to handle deletions)
$sql .= ", gallery_images=?";
$params[] = !empty($finalGalleryImages) ? json_encode($finalGalleryImages) : null;

$sql .= " WHERE id=?";
$params[] = $id;

// Generate types string dynamically
$types = "";
foreach ($params as $p) {
    if (is_int($p)) {
        $types .= "i";
    } elseif (is_float($p) || is_double($p)) {
        $types .= "d";
    } elseif (is_null($p)) {
        $types .= "s"; // NULL values are bound as string type
    } else {
        $types .= "s";
    }
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "SQL prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);

// Execute
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success", 
        "message" => "Event updated successfully",
        "gallery_images" => $finalGalleryImages
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update event: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>