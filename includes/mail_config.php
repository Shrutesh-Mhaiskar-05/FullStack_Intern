<?php
/**
 * SMTP Mail Configuration
 * 
 * For production, update these settings with your SMTP credentials.
 * 
 * Gmail SMTP (requires App Password):
 *   Host: smtp.gmail.com
 *   Port: 587
 *   Username: your@gmail.com
 *   Password: your-16-char-app-password
 * 
 * SendGrid:
 *   Host: smtp.sendgrid.net
 *   Port: 587
 *   Username: apikey
 *   Password: your-sendgrid-api-key
 * 
 * InfinityFree:
 *   Check your control panel for SMTP details
 */

define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'noreply@welcometoonlinebookstore.infinityfree.io');
define('SMTP_FROM_NAME', 'Online Bookstore');

/**
 * Mail sending function using PHPMailer
 */
function sendMail($to, $subject, $body) {
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';
    require_once __DIR__ . '/phpmailer/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        if (!empty(SMTP_USER) && !empty(SMTP_PASS)) {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
        } else {
            $mail->isMail();
        }

        $from = SMTP_FROM ?: 'noreply@bookstore.com';
        $mail->setFrom($from, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}
