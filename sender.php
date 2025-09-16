<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- EXECUTE THE NODE.JS SCRAPER ---
// This command runs the Node.js script and returns any output.
// The `shell_exec` function will hang until the Node.js script finishes.
$output = shell_exec('node ' . __DIR__ . '/scraper.js');
echo $output; // You can log the output for debugging

// --- CHECK FOR NEW LISTINGS AND SEND EMAIL ---
$newListingsFile = __DIR__ . '/new_listings.json';

if (file_exists($newListingsFile) && filesize($newListingsFile) > 2) {
    $listings = json_decode(file_get_contents($newListingsFile), true);

    if (!empty($listings)) {
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
            $mail->setFrom('info@hardcoded.ee', 'KV.ee Scraper');
            $mail->addAddress('markopaju92@gmail.com');

            // Content
            $mail->isHTML(false); // Set to true if you want to use HTML
            $mail->Subject = 'New listings from KV.ee!';

            $body = "New listings have been added to KV.ee:\n\n";
            foreach ($listings as $item) {
                $body .= "Title: " . $item['title'] . "\n";
                $body .= "Link: " . $item['link'] . "\n\n";
            }
            $mail->Body = $body;

            $mail->send();
            echo 'Email sent successfully!' . "\n";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}" . "\n";
        }
    }

    // --- OPTIONAL: CLEAN UP THE TEMP FILE ---
    //unlink($newListingsFile);
} else {
    echo "No new listings found." . "\n";
}
?>