<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");
require '../vendor/autoload.php'; // for Firebase JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$JWT_SECRET = "Pbt_Jwt_Secret"; // ⚡ move this to a config/env file

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Email and password are required"]);
    exit;
}

// Check user
$stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    exit;
}

// Generate JWT
$payload = [
    "id" => $user['id'],
    "email" => $user['email'],
    "exp" => time() + 3600  // 1 hour expiry
];
$token = JWT::encode($payload, $JWT_SECRET, 'HS256');

echo json_encode(["status" => "success", "message" => "Login successful", "token" => $token]);
?>
