<?php
// Set a timezone to ensure correct logging timestamps
date_default_timezone_set('Europe/Tallinn');

// Log the start of the PHP script
echo "[" . date('Y-m-d H:i:s') . "] Sender script starting...\n";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- EXECUTE THE NODE.JS SCRAPER ---
echo "[" . date('Y-m-d H:i:s') . "] Executing Node.js scraper...\n";
$output = shell_exec('node ' . __DIR__ . '/scraper.js 2>&1');
echo "[" . date('Y-m-d H:i:s') . "] Node.js script output:\n";
echo $output;

// --- CHECK FOR NEW LISTINGS AND SEND EMAIL ---
$newListingsFile = __DIR__ . '/new_listings.json';

if (file_exists($newListingsFile) && filesize($newListingsFile) > 2) {
    echo "[" . date('Y-m-d H:i:s') . "] New listings file exists and is not empty. Proceeding to send email.\n";
    $listings = json_decode(file_get_contents($newListingsFile), true);

    if (!empty($listings)) {
        echo "[" . date('Y-m-d H:i:s') . "] Found " . count($listings) . " new listings. Preparing email.\n";
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
            echo "[" . date('Y-m-d H:i:s') . "] SMTP settings configured.\n";

            // Recipients
            $mail->setFrom('your_email@gmail.com', 'KV.ee Scraper');
            $mail->addAddress('recipient_email@example.com');
            echo "[" . date('Y-m-d H:i:s') . "] Recipient email set.\n";

            // Content
            $mail->isHTML(false);
            $mail->Subject = 'New listings from KV.ee!';

            $body = "New listings have been added to KV.ee:\n\n";
            foreach ($listings as $item) {
                $body .= "Title: " . $item['title'] . "\n";
                $body .= "Link: " . $item['link'] . "\n\n";
            }
            $mail->Body = $body;

            $mail->send();
            echo "[" . date('Y-m-d H:i:s') . "] Email sent successfully!\n";
        } catch (Exception $e) {
            echo "[" . date('Y-m-d H:i:s') . "] ERROR: Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
        }
    }

    // unlink($newListingsFile); // You can uncomment this line to delete the temporary file after each run
} else {
    echo "[" . date('Y-m-d H:i:s') . "] No new listings found. No email sent.\n";
}
echo "[" . date('Y-m-d H:i:s') . "] Sender script finished.\n";
?>
