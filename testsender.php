<?php
// Set a timezone to ensure correct logging timestamps
date_default_timezone_set('Europe/Tallinn');

echo "[" . date('Y-m-d H:i:s') . "] Sender script starting...\n";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- TEMPORARILY MOCK DATA INSTEAD OF EXECUTING NODE.JS ---
// The previous line was: $output = shell_exec('node ' . __DIR__ . '/scraper.js 2>&1');
$listings = [
    [
        'title' => 'Mock Listing 1',
        'link' => 'https://www.kv.ee/mock-listing-1'
    ],
    [
        'title' => 'Mock Listing 2',
        'link' => 'https://www.kv.ee/mock-listing-2'
    ]
];
// We will use this hardcoded data to test the mail sending logic below.

// --- PROCEED WITH EMAIL SENDING ---
// This part of the code remains the same, but now it uses the mock data
if (!empty($listings)) {
    echo "[" . date('Y-m-d H:i:s') . "] Found " . count($listings) . " mock listings. Preparing email.\n";
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
} else {
    echo "[" . date('Y-m-d H:i:s') . "] No new listings found. No email sent.\n";
}
echo "[" . date('Y-m-d H:i:s') . "] Sender script finished.\n";
?>