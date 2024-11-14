<?php
// api/mailgun_api.php

require '../vendor/autoload.php';
use Mailgun\Mailgun;

function sendCompletionEmail($toEmail, $taskTitle) {
    $mgClient = Mailgun::create('YOUR_API_KEY'); // Replace with your API key
    $domain = "YOUR_DOMAIN_NAME"; // Replace with your Mailgun domain

    $mgClient->messages()->send($domain, [
        'from'    => 'no-reply@yourdomain.com',
        'to'      => $toEmail,
        'subject' => 'Task Completed',
        'text'    => "Congratulations! You've completed the task: $taskTitle"
    ]);
}
?>
