<?php
// includes/email.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

/**
 * Send email using PHPMailer with SMTP
 */
if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $body, $isHTML = true, $fromName = 'TechHaven Electronics')
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Or your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'fabriceraymond53@gmail.com'; // Your email
            $mail->Password   = 'uhfnkxjsrkkzirnn'; // Your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('noreply@techhaven.com', $fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            if (!$isHTML) {
                $mail->AltBody = strip_tags($body);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}

/**
 * Send contact notification to admin
 */
if (!function_exists('sendContactNotification')) {
    function sendContactNotification($name, $email, $subject, $message, $message_id)
    {
        $admin_email = "fabriceraymond53@gmail.com"; // Use your email as admin

        $email_subject = "📧 New Contact Message #$message_id: $subject";

        $email_body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3B82F6; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; }
                .footer { background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .message-box { background: white; border-left: 4px solid #3B82F6; padding: 15px; margin: 15px 0; }
                .info-box { background: #e8f4fd; border: 1px solid #b3d9ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Contact Form Submission</h1>
                    <p>Message ID: #$message_id</p>
                </div>
                <div class='content'>
                    <div class='info-box'>
                        <h3>Contact Details:</h3>
                        <p><strong>Name:</strong> $name</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Subject:</strong> $subject</p>
                    </div>
                    
                    <h3>Message:</h3>
                    <div class='message-box'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                    
                    <p><strong>Submitted:</strong> " . date('F j, Y \a\t g:i A') . "</p>
                    <p><strong>IP Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "</p>
                </div>
                <div class='footer'>
                    <p>This email was sent from the contact form on Wima Store Electronics</p>
                    <p>Please respond to this inquiry within 24 hours.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return sendEmail($admin_email, $email_subject, $email_body, true, 'TechHaven Contact Form');
    }
}

/**
 * Send confirmation email to user
 */
if (!function_exists('sendUserConfirmation')) {
    function sendUserConfirmation($name, $email, $subject, $message)
    {
        $user_subject = "Thank you for contacting TechHaven Electronics";

        $user_body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #10B981; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; }
                .footer { background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .message-box { background: white; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .steps { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Thank You for Contacting Us!</h1>
                </div>
                <div class='content'>
                    <p>Dear $name,</p>
                    
                    <p>Thank you for reaching out to TechHaven Electronics! We have received your message and our team will review it shortly.</p>
                    
                    <div class='message-box'>
                        <h3>Your Message Details:</h3>
                        <p><strong>Subject:</strong> $subject</p>
                        <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    
                    <div class='steps'>
                        <h3>What happens next?</h3>
                        <ul>
                            <li>Our team will review your message</li>
                            <li>We'll respond within 24 hours during business days</li>
                            <li>For urgent matters, call us at +250780088390</li>
                        </ul>
                    </div>
                    
                    <p><strong>Business Hours:</strong><br>
                    Monday - Friday: 9:00 AM - 6:00 PM<br>
                    Saturday: 10:00 AM - 4:00 PM</p>
                </div>
                <div class='footer'>
                    <p>Best regards,<br><strong>The TechHaven Electronics Team</strong></p>
                    <p>Email: info@techhaven.com | Phone: +250780088390</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return sendEmail($email, $user_subject, $user_body, true, 'TechHaven Electronics');
    }
}
