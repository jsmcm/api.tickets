<?php

declare(strict_types=1);

namespace App\Services\MailDownloader;

use EmailReplyParser\Parser\EmailParser;
use App\Services\MailDownloader\Mail;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Download
{

    private $mailbox = null;

    public function __construct(
        private string $username,
        private string $password,
        private $callbackJob,
        private string $host,
        private int $port=143,
        private string $protocol="imap"
        )
    {


        set_time_limit(300);
        imap_timeout(IMAP_OPENTIMEOUT, 300); // Set open timeout to 300 seconds
        imap_timeout(IMAP_READTIMEOUT, 300); // Set read timeout to 300 seconds


        $connectionType = "notls";
        if ($port == 993) {
            $connectionType = "ssl";
        }
        

        Log::debug("host: ".$host);
        Log::debug("protocol: ".$protocol);
        Log::debug("port: ".$port);
        Log::debug("connectionType: ".$connectionType);
        Log::debug("username: ".$username);
        Log::debug("password: ".$password);


        $this->mailbox = new Mailbox(
            '{'.$host.':'.$port.'/'.$protocol.'/'.$connectionType.'}INBOX', // IMAP server and mailbox folder
            $username, // Username for the before configured mailbox
            $password // Password for the before configured username
        );

        Log::debug("mailbox: ".print_r($this->mailbox , true));
        
    }


    private function convertGmailToBase($email)
    {

        $email = strtolower($email);

        $mailUser = "";
        $mailDomain = "";

        $mailUser = substr($email, 0, strpos($email, "@"));
        $mailDomain = substr($email, strpos($email, "@") +  1);

        if ($mailDomain == "gmail.com" || $mailDomain == "googlemail.com") {

            $mailUser = str_replace(".", "", $mailUser);

            $email = $mailUser."@".$mailDomain;
        }

        return $email;
    }

    private function parseEmailHeaders($headers)
    {
        $result = [];
        $lines = explode("\n", $headers);
        $lastHeader = "";
        
        foreach ($lines as $line) {
            // If the line is a continuation of the previous line.
            if (isset($line[0]) && ($line[0] === ' ' || $line[0] === "\t")) {
                $result[$lastHeader] .= ' ' . trim($line);
            } else {
                $parts = explode(':', $line, 2);
                if (count($parts) == 2) {
                    $lastHeader = trim($parts[0]);
                    $result[$lastHeader] = trim($parts[1]);
                }
            }
        }
    
        return $result;
    }


    private function fetchEmail($mailbox, $mail_id)
    {

        // this is the mail object we'll return
        $mail = new Mail();

        // Log::debug("in fetchEmail, gettingMail");

        $email = $mailbox->getMail(
            $mail_id, // ID of the email, you want to get
            true //false // Do NOT mark emails as seen (optional)
        );


        // Log::debug("got it");

        $header = $mailbox->getMailHeader($mail_id)->headersRaw;

        $mail->headers($this->parseEmailHeaders($header));

        $ip_matches = [];
        preg_match_all('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $header, $ip_matches);
     
        $allIps = [];
	    $ipCounter = 0;
        foreach ($ip_matches[0] as $ip) {
            
            $ip = filter_var(
                $ip, 
                FILTER_VALIDATE_IP, 
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE
            );

            if ($ip !== false) {
                if ($ipCounter == 0) {
                    $allIps[] = $ip;
                }

                $ipCounter++;
            }
           
        }

        $allIps = array_unique($allIps);
        $mail->ips($allIps);


        if(isset($email->to)) {
            $mail->to($email->to);
        }


        if(isset($email->cc)) {
            $mail->cc($email->cc);
        }

        $mail->fromName((string) $email->fromName);
        $mail->fromAddress((string) $email->fromAddress);
        $mail->fromBaseAddress((string) $this->convertGmailToBase($email->fromAddress));

        $mail->subject((string) $email->subject);    
        $mail->messageId((string) $email->messageId);


        $mail->textPlain($email->textPlain);

        $emailParser = new EmailParser();
        $emailFragments = null;

        if (isset($email->textHtml) && $email->textHtml != "" ) {
            $mail->textHtml($email->textHtml);
            $emailFragments = $emailParser->parse($email->textHtml);
        } else {
            $mail->textPlain($email->textPlain);
            $emailFragments = $emailParser->parse($email->textPlain);
        }

        
        
	    //file_put_contents(dirname(__DIR__)."/tmp/support_emails/".$fileTimeStamp."/".$logTimeStamp.".txt", "emailParser: ".print_r($emailParser, true)."\r\n", FILE_APPEND);
        
        $fragments = $emailFragments->getFragments();
	    //file_put_contents(dirname(__DIR__)."/tmp/support_emails/".$fileTimeStamp."/".$logTimeStamp.".txt", "fragments: ".print_r($fragments, true)."\r\n", FILE_APPEND);
        

        $message = "";

        // Log::debug("loading fragments");
        foreach($fragments as $fragment) {
            $message .= $fragment->getContent();
        }

        // Log::debug("got fragments");
        $mail->message($message);
        

        //Log::debug("checking for attachments");
        if (!$mailbox->getAttachmentsIgnore()) {  

            //Log::debug("really checking for attachments");
            if ($email->hasAttachments()) {

                // we set blank message and subjects here because if they
                // are blank but there is an attachment we do want that ticket
                if ($message == "") {
                    $mail->message("[BLANK BODY]");
                }

                if ($mail->subject() == "") {
                    $mail->subject("[BLANK EMAIL]");
                }


                $attachmentsArray = [];

                $attachments = $email->getAttachments();

                foreach ($attachments as $attachment) {
                    
                    $attachmentObject = new \stdClass();

                    //$path = storage_path("/app/".(string) $attachment->id);
                    //$attachment->setFilePath($path);

                    $randomString = date("Ydm_His")."_".Str::random(48);
                    $path = "attachements/temp/".$randomString."_".$attachment->id."_".$attachment->name;
    
                    // Log::debug("move: ".$attachment->path." to ".$path);
                    Storage::disk("s3_file_storage")->put(
                        $path,
                        $attachment->getContents(),
                        "public"
                    );


                    //if ($attachment->saveToDisk()) {
                        //Log::debug("attachment saved as /tmp/".(string) $attachment->id);
                        $attachmentObject->id               = (string) $attachment->id;
                        $attachmentObject->path             = $path;
                        $attachmentObject->randomString     = $randomString;
                        $attachmentObject->name             = $attachment->name;
                        $attachmentObject->sizeInBytes      = $attachment->sizeInBytes;
                        $attachmentObject->mime             = $attachment->mime;
                        $attachmentObject->fileExtension    = $attachment->fileExtension;

                        $attachmentsArray[]                 = $attachmentObject;
                    //}

                }

                
                //Log::debug("getAttachments:");
                // Log::debug(print_r($email->getAttachments(), true));;
                // Log::debug("Has attachments, storing");
                $mail->attachments($attachmentsArray);
                //Log::debug("stored");
            } else {
                        
                if ($message == "" && $mail->subject() == "") {
                    return null;
                }

                if ($message == "") {
                    $mail->message($mail->subject());
                }

                if ($mail->subject() == "") {
                    $mail->subject("[BLANK]");
                }

            }
        
        }

        return $mail;

    }








    private function fetch()
    {
    
        $mail_ids = null;

        try {
            
            $mail_ids = $this->mailbox->searchMailbox('ALL');
            
        } catch (ConnectionException $ex) {
            die('IMAP connection failed: '.$ex->getMessage());
        } catch (\Exception $ex) {
            die('An error occured: '.$ex->getMessage());
        }


        Log::debug("got mail ids: ".print_r($mail_ids, true));

        $numberToGet = 0;
        if (count($mail_ids) > 0) {
            foreach ($mail_ids as $mail_id) {
                
                // Log::debug("fetchEmail: ".$mail_id);
                if($mail = $this->fetchEmail($this->mailbox, $mail_id)) {  
                    

                    Log::debug("got mail");
                    Log::debug("DT: ".print_r($mail->headers(), true));

                    if (isset($mail->headers()["Envelope-to"])) {
                        Log::debug("DT: ".$mail->headers()["Envelope-to"]);
                    } else if (isset($mail->headers()["Delivered-To"])) {
                        Log::debug("DT: ".$mail->headers()["Delivered-To"]);
                    }



                    Log::debug("to: ".$mail->headers()["To"]);

                    Log::debug("from: ".$mail->headers()["From"]);

                    Log::debug("subject: ".$mail->headers()["Subject"]);

                    $returnPath = "";
                    if (isset($mail->headers()["Return-Path"])) {
                        $returnPath = $mail->headers()["Return-Path"];
                    } else if (isset($mail->headers()["Return-path"])) {
                        $returnPath = $mail->headers()["Return-path"];
                    }
                    Log::debug("returnPath: ".$returnPath);

                    Log::debug(print_r($mail, true));

                    $sentTo = "";
                    if (isset($mail->headers()["Envelope-to"])) {
                        $sentTo = $mail->headers()["Envelope-to"];
                    } else if (isset($mail->headers()["Delivered-To"])) {
                        $sentTo = $mail->headers()["Delivered-To"];
                    }


                    $mailArray = [
                        "sentTo"        => $sentTo,
                        "returnPath"    => $returnPath,
                        "fromAddress"   => $mail->fromAddress(),
                        "subject"       => $mail->subject(),
                        "ip"            => $mail->ips()[0],
                        "fromAddress"   => $mail->fromAddress(),
                        "fromName"      => $mail->fromName(),
                        "message"       => $mail->message(),
                        "attachments"   => $mail->attachments()
                    ];



                    // Ignore bounces
                    if ($returnPath != "<>") {
                        $this->callbackJob::dispatch($mailArray)
                            ->delay(now()
                            ->addSeconds(15));
                    }
                    


                    if ($numberToGet++ >= config("tickets.download_per_round")) {
                        break;
                    }
                }

                if (config("tickets.delete_after_download") == true) {
                    Log::debug("delete_after_download is true...");
                    $this->mailbox->deleteMail($mail_id);
                } else {
                    Log::debug("delete_after_download is false...");
                }
                
            }
        }

    }

    public function download()
    {

        // Log::debug("calling fetch...");
        $this->fetch();

        $this->mailbox->expungeDeletedMails();
        $this->mailbox->disconnect();


        
    }

}
