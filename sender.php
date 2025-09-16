<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- EXECUTE THE NODE.JS SCRAPER ---
$output = shell_exec('node ' . __DIR__ . '/scraper.js');
echo $output;

// --- CHECK FOR NEW LISTINGS AND SEND EMAIL ---
$newListingsFile = __DIR__ . '/new_listings.txt';

// Check if the file is not empty
if (file_exists($newListingsFile) && filesize($newListingsFile) > 0) {
    // Read the file line by line
    $newLinks = file($newListingsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!empty($newLinks)) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.zone.eu'; // or your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'info@hardcoded.ee';
            $mail->Password = 'blasonsimlen'; // Use an App Password, NOT your main password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your_email@gmail.com', 'KV.ee Scraper');
            $mail->addAddress('recipient_email@example.com');

            // Content
            $mail->isHTML(false);
            $mail->Subject = 'New listings from KV.ee!';

            $body = "New listings have been added to KV.ee:\n\n";
            foreach ($newLinks as $link) {
                $body .= "- " . $link . "\n";
            }
            $mail->Body = $body;

            $mail->send();
            echo 'Email sent successfully!' . "\n";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}" . "\n";
        }
    }

    // --- OPTIONAL: CLEAN UP THE TEMP FILE ---
    // unlink($newListingsFile);
} else {
    echo "No new listings found." . "\n";
}
?>