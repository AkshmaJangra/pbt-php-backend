<?php
include_once(__DIR__ . "/../cors.php");
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$JWT_SECRET = "Pbt_Jwt_Secret";

function verifyToken($token) {
    global $JWT_SECRET;
    try {
        $decoded = JWT::decode($token, new Key($JWT_SECRET, 'HS256'));
        return $decoded; // returns user info
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
        exit;
    }
}
?>
