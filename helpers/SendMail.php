<?php
/**
 * Giải thích mã:
 * - Tiện ích dùng chung cho nghiệp vụ gửi email.
 * - Đóng gói logic lặp lại để controller/model tập trung vào luồng nghiệp vụ chính.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class SendMail {
    /**
     * Gửi email HTML qua SMTP bằng PHPMailer, dùng cấu hình SMTP lấy từ biến môi trường.
     */
    public static function send($to, $subject, $htmlContent) {
        $to = trim((string)$to);
        $from = trim((string)($_ENV['EMAIL_USER'] ?? ''));
        $password = trim((string)($_ENV['EMAIL_PASSWORD'] ?? ''));
        $fromName = trim((string)($_ENV['EMAIL_FROM_NAME'] ?? ($_ENV['APP_NAME'] ?? 'Job Portal')));
        $smtpHost = trim((string)($_ENV['SMTP_HOST'] ?? 'smtp.gmail.com'));
        $smtpPort = (int)($_ENV['SMTP_PORT'] ?? 587);
        $smtpEncryption = strtolower(trim((string)($_ENV['SMTP_ENCRYPTION'] ?? 'tls')));

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('Lỗi SendMail: email người nhận không hợp lệ');
            return false;
        }

        if (empty($from) || empty($password) || $password === 'your-app-password') {
            error_log('Lỗi SendMail: EMAIL_USER/EMAIL_PASSWORD chưa được cấu hình');
            return false;
        }

        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $from;
            $mail->Password = $password;
            $mail->Port = $smtpPort;

            if ($smtpEncryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->CharSet = 'UTF-8';
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = (string)$subject;
            $mail->Body = (string)$htmlContent;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", (string)$htmlContent));

            return $mail->send();
        } catch (Exception $e) {
            error_log('Lỗi SendMail: ' . $e->getMessage());
            return false;
        }
    }
}
