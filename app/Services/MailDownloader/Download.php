<?php

declare(strict_types=1);

namespace App\Services\MailDownloader;

use EmailReplyParser\Parser\EmailParser;
use App\Services\MailDownloader\Mail;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use App\Models\Ban;
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
        private bool $deleteAfterFetch=true
        )
    {

        set_time_limit(300);
        imap_timeout(IMAP_OPENTIMEOUT, 300); // Set open timeout to 300 seconds
        imap_timeout(IMAP_READTIMEOUT, 300); // Set read timeout to 300 seconds


        $connectionType = "notls";
        if ($port >= 993) {
            $connectionType = "ssl";
        }

        $protocol = "pop";
        if ($port == 143 || $port == 993) {
            $protocol = "imap";
        }

        $this->mailbox = new Mailbox(
            '{'.$host.':'.$port.'/'.$protocol.'/'.$connectionType.'}INBOX', // IMAP server and mailbox folder
            $username, // Username for the before configured mailbox
            $password // Password for the before configured username
        );

//         imap_errors();
// imap_alerts();

    }


    private function removeReply($message)
    {
        $body = strpos($message, "<body");
        if ($body) {
            $bodyEnd = strpos($message, ">", $body);
            $message = substr($message, $bodyEnd + 1);
        }

        $quoteStart = strpos($message, "<div class=\"moz-cite-prefix\">");
        if ($quoteStart) {
            $message = substr($message, 0, $quoteStart);
        }

        $quoteStart = strpos($message, "<div class=\"gmail_quote gmail_quote_container\">");
        if ($quoteStart) {
            $message = substr($message, 0, $quoteStart);
        }

        $quoteStart = strpos($message, "<div class=\"gmail_quote\"");
        if ($quoteStart) {
            $quoteEnd = strpos($message, ">", $quoteStart);
            $message = substr($message, 0, $quoteEnd);
        }


        $pattern = '/On \d{4}\/\d{2}\/\d{2} \d{2}:\d{2}, [A-Za-z]+ [A-Za-z]+ wrote:/';
        if (preg_match($pattern, $message, $matches, PREG_OFFSET_CAPTURE)) {
            $message = substr($message, 0, intVal($matches[0][1]));
        }

        $message = strip_tags($message);

        $pattern = '/Get \w+ for \w+/';
        if (preg_match($pattern, $message, $matches, PREG_OFFSET_CAPTURE)) {
            $message = substr($message, 0, intVal($matches[0][1]));
        }


        return trim($message);
    }




    private function convertGmailToBase($email)
    {
	    if ($email == "") {
		    return "";
	    }

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

	    try {
        	$email = $mailbox->getMail(
                $mail_id, // ID of the email, you want to get
                true //false // Do NOT mark emails as seen (optional)
		    );
        } catch (\Exception $e) {
            Log::debug("exception: ".$e->getMessage());
            return false;
        }

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

        $fragments = $emailFragments->getFragments();	    

        $message = "";

        foreach($fragments as $fragment) {

            $messagePart = $fragment->getContent();

            Log::debug("-------------------------------------------------");
            Log::debug("PRE STRIP FRAGMENT: ");
            Log::debug(print_r($messagePart, true));
            Log::debug("=================================================");
            
            $messagePart = $this->removeReply($messagePart);

            Log::debug("-------------------------------------------------");
            Log::debug("POST STRIP FRAGMENT: ");
            Log::debug(print_r($messagePart, true));
            Log::debug("=================================================");
            
            $message .= $messagePart;
        }

        $mail->message($message);

        if (!$mailbox->getAttachmentsIgnore()) {  

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

                    $randomString = date("Ydm_His")."_".Str::random(48);
                    $path = "attachements/temp/".$randomString."_".$attachment->id."_".$attachment->name;
    
                    Storage::disk("s3_file_storage")->put(
                        $path,
                        $attachment->getContents(),
                        "public"
                    );


                    $attachmentObject->id               = (string) $attachment->id;
                    $attachmentObject->path             = $path;
                    $attachmentObject->randomString     = $randomString;
                    $attachmentObject->name             = $attachment->name;
                    $attachmentObject->sizeInBytes      = $attachment->sizeInBytes;
                    $attachmentObject->mime             = $attachment->mime;
                    $attachmentObject->fileExtension    = $attachment->fileExtension;

                    $attachmentsArray[]                 = $attachmentObject;

                }

                $mail->attachments($attachmentsArray);
              
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
	Log::debug("in fetch");

        try {


		$seen = "UNSEEN";
		if ($this->deleteAfterFetch) {
			$seen = "ALL";
		}

		$mail_ids = $this->mailbox->searchMailbox($seen);
	} catch (ConnectionException $ex) {
            Log::error($ex->getMessage());
            throw new \Exception('IMAP connection failed: '.$ex->getMessage());
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            throw new \Exception('An error occured: '.$ex->getMessage());
        }

	    $numberToGet = 0;
        if (count($mail_ids) > 0) {
            foreach ($mail_ids as $mail_id) {
        	Log::debug("fetchEmail id: ".$mail_id);        
                if($mail = $this->fetchEmail($this->mailbox, $mail_id)) {  
                    Log::debug("fetch succeeded");

                    $returnPath = "";
                    if (isset($mail->headers()["Return-Path"])) {
                        $returnPath = $mail->headers()["Return-Path"];
                    } else if (isset($mail->headers()["Return-path"])) {
                        $returnPath = $mail->headers()["Return-path"];
                    }

                    $sentTo = "";
                    if (isset($mail->headers()["Envelope-to"])) {
                        $sentTo = $mail->headers()["Envelope-to"];
		    } else if (isset($mail->headers()["To"])) {
                        $sentTo = $mail->headers()["To"];
                    } else if (isset($mail->headers()["Delivered-To"])) {
                        $sentTo = $mail->headers()["Delivered-To"];
                    }

                    $sentTo = $this->parseEmailAddress($sentTo);

		             //if ($sentTo != $this->username) {
                    if ($sentTo == "john@pricedrop.co.za") {
                        continue;
                    }

                    // REMOVE
                    if ($sentTo == "info@babyandtoddler.co.za") {
                        $sentTo = "support@babyandtoddler.co.za";
                    }

                    $mailArray = [
                        "sentTo"        => $sentTo,
                        "returnPath"    => $returnPath,
                        "fromAddress"   => $mail->fromAddress(),
                        "subject"       => $mail->subject(),
                        "ip"            => $mail->ips()[0]??"",
                        "fromAddress"   => $mail->fromAddress(),
                        "fromName"      => $mail->fromName(),
			            "message"       => $mail->message(),
                        "attachments"   => $mail->attachments()
                    ];

                    $processMail = true;

                    if ($mailArray["returnPath"] == "<no-reply@amazonses.com>" && $mailArray["fromAddress"] == "complaints@email-abuse.amazonses.com") {
                        $mailArray["message"] = substr($mailArray["message"], 0, strpos($mailArray["message"], "\n"));
                    }

                    // Ignore bounces
                    if ($returnPath == "<>") {
                        $processMail = false;
                    }

                    $ban = Ban::where("email", $mailArray["fromAddress"])->first();
                    if (!empty($ban)) {
                        $processMail = false; 
                    }

		    if ($processMail) {
			    Log::debug("queuein mail: ".print_r($mailArray, true));
			    Log::debug(print_r($this->callbackJob, true));
                        $this->callbackJob::dispatch($mailArray)
                            ->delay(now()
                            ->addSeconds(15));
                    }

		    if ($numberToGet++ >= config("tickets.download_per_round")) {
                        break;
                    }
		} else {
			Log::debug("Fetch failed for ".$mail_id);
		}

		if ($this->deleteAfterFetch) {
                    $this->mailbox->deleteMail($mail_id);
                }
                
            }
        }

    }

    function parseEmailAddress($To)
    {
	    $emailAddress = $To;
	    $lt = strpos($To, "<");
	    if ($lt) {
		    $emailAddress = substr($To, $lt);
	    }

	    $emailAddress = str_replace("\"", "", $emailAddress);
	    $emailAddress = str_replace("<", "", $emailAddress);
	    $emailAddress = str_replace(">", "", $emailAddress);

	   return trim($emailAddress); 
    }

    public function download()
    {
        $this->fetch();

        $this->mailbox->expungeDeletedMails();
        $this->mailbox->disconnect();
    }

}
