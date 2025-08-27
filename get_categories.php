<?php


// CREATE TABLE categories (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     category_name VARCHAR(255) NOT NULL
// );

include "conn.php"; // Include database connection
header("Access-Control-Allow-Origin: *"); // Allow all origins (for development)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Fetch categories
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "SQL error: " . $conn->error]);
    exit;
}

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Return JSON response
echo json_encode($categories);
$conn->close();
