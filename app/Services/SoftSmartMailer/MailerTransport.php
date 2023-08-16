<?php

declare(strict_types=1);

namespace App\Services\SoftSmartMailer;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

use GuzzleHttp\Client;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Log;


/**
 * This class is used to send transactional emails
 */
class MailerTransport extends AbstractTransport

{


    public function __construct(protected array $config)
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return "softsmartmailer";
    }


    public function doSend(SentMessage $message): void
    {

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        Log::write("debug", "config: ".print_r($this->config, true));
 
        Log::write("debug", "subject: ".$email->getSubject());
        Log::write("debug", "getTextBody: ".$email->getTextBody());

        $json = json_decode($email->getTextBody());
        
        Log::write("debug", "json: ".print_r($json, true));


        // $client = new Client();
        // $response = $client->post('https://mailer.softsmart.co.za/api/transactional-mails/send', [
        //     'body' => json_encode([
        //         'mailer'    => $mailer,
        //         'mail_name' => $mailName,
        //         'subject'   => $subject,
        //         'from'      => $fromAddress,
        //         'to'        => $toAddress,
        //         'store'     => true,
        //         'replacements' => $replacements
        //     ]),
        //     'headers' => [
        //         'Authorization' => 'Bearer '.env("MAILCOACH_API_KEY"),
        //         'Accept' => 'application/json',
        //         'Content-Type' => 'application/json'
        //     ]
        // ]);
    }

}
