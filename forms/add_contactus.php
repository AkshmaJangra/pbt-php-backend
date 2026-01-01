<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
// Unique 5-char contactus ID generator
function generateUniqueContactusId($conn) {
    do {
        $id = substr(strtoupper(bin2hex(random_bytes(3))), 0, 5);
        $check = $conn->query("SELECT COUNT(*) as cnt FROM contactus WHERE id='$id'");
        $row = $check->fetch_assoc();
    } while ($row['cnt'] > 0);
    return $id;
}
$input = json_decode(file_get_contents("php://input"), true);

$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$mobile = $input['mobile'] ?? '';
$message = $input['message'] ?? '';

// generate unique ID
$id = generateUniqueContactusId($conn);

// insert contactus
$sql ="INSERT INTO contactus (id, name, email, mobile, message) VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("sssss", $id, $name, $email, $mobile, $message);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "ContactUS form added successfully",
        "data" => [
            "id" => $id,
            "name" => $name,
            "email" => $email,
            "mobile" => $mobile,
            "message" => $message,
           
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add contactus form"]);
}
?>
