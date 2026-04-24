<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Manual inclusion because it's not in the vendor/autoload
require_once __DIR__ . '/../Libraries/PHPMailer/Exception.php';
require_once __DIR__ . '/../Libraries/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../Libraries/PHPMailer/SMTP.php';

if (!function_exists('send_custom_email')) {
    /**
     * Send an email using PHPMailer (Anti-Spam Configuration)
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email content (HTML)
     * @param string|null $altBody Plain text version of the body
     * @return bool True if sent, false otherwise
     */
    function send_custom_email($to, $subject, $body, $altBody = null)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host', 'smtp.hostinger.com');
            $mail->SMTPAuth = true;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = config('mail.mailers.smtp.encryption', PHPMailer::ENCRYPTION_SMTPS);
            $mail->Port = config('mail.mailers.smtp.port', 465);

            // Hostinger/Shared Hosting SSL Fix
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Anti-Spam Headers
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(config('mail.from.address', 'noreply@eurotaxisystem.site'), config('mail.from.name', 'Euro Taxi System'));
            $mail->addAddress($to);
            $mail->addReplyTo(config('mail.from.address', 'support@eurotaxisystem.site'), config('mail.from.name', 'Support'));

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);

            // Additional headers to avoid spam filters
            $mail->addCustomHeader('X-Priority', '3');
            $mail->addCustomHeader('X-Mailer', 'EurotaxisystemPHPMailer');

            \Log::info("Attempting to send email to: {$to} using Host: {$mail->Host} Port: {$mail->Port}");

            $sent = $mail->send();
            if ($sent) {
                \Log::info("Email successfully sent to: {$to}");
            }
            return $sent;
        } catch (Exception $e) {
            \Log::error("PHPMailer Exception for {$to}: " . $e->getMessage());
            \Log::error("SMTP Error: " . $mail->ErrorInfo);
            return false;
        } catch (\Throwable $t) {
            \Log::error("Fatal Error sending email to {$to}: " . $t->getMessage());
            return false;
        }
    }
}
