<?php
include_once(__DIR__ . "/cors.php"); // keep your CORS file if needed
require __DIR__ . '/vendor/autoload.php'; // autoload PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $to = $data['to'];
    $subject = $data['subject'];
    $message = $data['message'];

    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'mail.panavbiotech.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@panavbiotech.com';
        $mail->Password = 'Panavbiotech@2354'; // 👈 replace with the real password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // use 'ssl' or PHPMailer constant
        $mail->Port = 465;

        // From / To
        $mail->setFrom('noreply@panavbiotech.com', 'Panav Biotech');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();

        echo json_encode(["status" => "success", "message" => "Email sent successfully"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
    }
}
?>
