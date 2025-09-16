<?php
header("Content-Type: application/json");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jobTitle = $_POST['jobTitle'] ?? '';
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $message  = $_POST['message'] ?? '';

    try {
        $mail = new PHPMailer(true);

        // SMTP settings (Brevo)
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'akshmajangra999@gmail.com'; // Brevo username
        $mail->Password   = 'XR73A9vQ6xGzdFjr';          // Brevo SMTP key
        $mail->SMTPSecure = 'tls';                       // TLS encryption
        $mail->Port       = 587;

        // Sender (same as Brevo username to avoid spam issues)
        $mail->setFrom('akshmajangra999@gmail.com', 'Job Portal');

        // Recipient (Admin = you)
        $mail->addAddress('akshmajangra999@gmail.com', 'Admin');

        // (Optional) Reply-to as applicant
        if (!empty($email)) {
            $mail->addReplyTo($email, $name);
        }

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "📩 New Job Application: " . $jobTitle;
        $mail->Body = "
            <h2>New Job Application Received</h2>
            <p><b>Name:</b> $name</p>
            <p><b>Email:</b> $email</p>
            <p><b>Phone:</b> $phone</p>
            <p><b>Message:</b> $message</p>
        ";

        // Attach Resume if uploaded
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $mail->addAttachment($_FILES['resume']['tmp_name'], $_FILES['resume']['name']);
        }

        $mail->send();
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $mail->ErrorInfo]);
    }
}
