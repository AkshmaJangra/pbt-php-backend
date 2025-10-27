<?php
include_once(__DIR__ . "/cors.php"); // if you use CORS

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $to = $data['to'];
    $subject = $data['subject'];
    $message = $data['message'];
    $from = $data['from'] ?? "no-reply@yourdomain.com";

    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(["status" => "success", "message" => "Email sent successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to send email"]);
    }
}
?>
