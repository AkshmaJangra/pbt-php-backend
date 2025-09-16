<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// ✅ Common upload folder (one folder for all pages)
$uploadDir = __DIR__ . "/../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // auto-create if missing
}

// Check if record exists (we only want one row)
$check = $conn->query("SELECT * FROM jobpostings LIMIT 1");
$existing = $check->fetch_assoc();

$image1 = $existing['image1'] ?? null;
// $image2 = $existing['image2'] ?? null;
$link1  = $_POST['link1'] ?? ($existing['link1'] ?? "");
// $link2  = $_POST['link2'] ?? ($existing['link2'] ?? "");

// ✅ Handle file uploads
if (!empty($_FILES['image1']['name'])) {
    $filename = time() . "_1_" . basename($_FILES['image1']['name']);
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['image1']['tmp_name'], $targetPath)) {
        $image1 = "uploads/" . $filename; // store relative path in DB
    }
}
// if (!empty($_FILES['image2']['name'])) {
//     $filename = time() . "_2_" . basename($_FILES['image2']['name']);
//     $targetPath = $uploadDir . $filename;
//     if (move_uploaded_file($_FILES['image2']['tmp_name'], $targetPath)) {
//         $image2 = "uploads/" . $filename; // ✅ corrected to $image2
//     }
// }

// Insert or Update
if ($existing) {
    $id = $existing['id'];
    $sql = "UPDATE jobpostings 
            SET image1='$image1', link1='$link1' WHERE id=$id";
} else {
    $sql = "INSERT INTO jobpostings (image1, link1) 
            VALUES ('$image1', '$link1')";
}

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => $existing ? "Job posting updated successfully" : "Job posting created successfully",
        "data" => [
            "image1" => ["url" => $image1, "link" => $link1]
            // "image2" => ["url" => $image2, "link" => $link2]
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$conn->close();
