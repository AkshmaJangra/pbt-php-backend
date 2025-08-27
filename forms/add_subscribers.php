<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
// Unique 5-char subscriber ID generator
function generateUniqueSubscriberId($conn) {
    do {
        $id = substr(strtoupper(bin2hex(random_bytes(3))), 0, 4);
        $check = $conn->query("SELECT COUNT(*) as cnt FROM newsletter WHERE id='$id'");
        $row = $check->fetch_assoc();
    } while ($row['cnt'] > 0);
    return $id;
}

$input = json_decode(file_get_contents("php://input"), true);

$name = $input['name'] ?? '';
$email = $input['email'] ?? '';

// generate unique ID
$id = generateUniqueSubscriberId($conn);

// insert subscriber
$sql ="INSERT INTO newsletter (id, name, email) VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("sss", $id, $name, $email);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Subscriber added successfully",
        "data" => [
            "id" => $id,
            "name" => $name,
            "email" => $email,
         
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add subscriber"]);
}
?>
