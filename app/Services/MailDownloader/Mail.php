<?php

declare(strict_types=1);

namespace App\Services\MailDownloader;

/**
 * This is the mail object we'll pass back once 
 * we've downloaded the mail
 */
class Mail
{
    private $subject        = "";
    private $messageId      = "";
    private $message        = "";
    private $textHtml       = "";
    private $textPlain      = "";
    private $attachments    = [];
    private $ips            = [];
    private $to             = [];
    private $cc             = [];
    
    private $headers        = [];
    private $fromName       = "";
    private $fromAddress    = "";
    private $fromBaseAddress= "";


    public function headers(Array $headers = [])
    {
        if (!empty($headers)) {
            $this->headers = $headers;
        }
        return $this->headers;
    }

    public function messageId(string $messageId = "")
    {
        if (!empty($messageId)) {
            $this->messageId = $messageId;
        }
        return $this->messageId;
    }


    public function fromName(string $fromName = "")
    {
        if (!empty($fromName)) {
            $this->fromName = $fromName;
        }
        return $this->fromName;
    }


    public function fromAddress(string $fromAddress = "")
    {
        if (!empty($fromAddress)) {
            $this->fromAddress = $fromAddress;
        }
        return $this->fromAddress;
    }


    public function fromBaseAddress(string $fromBaseAddress = "")
    {
        if (!empty($fromBaseAddress)) {
            $this->fromBaseAddress = $fromBaseAddress;
        }
        return $this->fromBaseAddress;
    }


    public function attachments($attachments = [])
    {
        if (!empty($attachments)) {
            $this->attachments = $attachments;
        }
        return $this->attachments;
    }

    public function to(Array $to = [])
    {
        if (!empty($to)) {
            $this->to = $to;
        }
        return $this->to;
    }


    public function cc(Array $cc = [])
    {
        if (!empty($cc)) {
            $this->cc = $cc;
        }
        return $this->cc;
    }


    public function ips(Array $ips = [])
    {
        if (!empty($ips)) {
            $this->ips = $ips;
        }
        return $this->ips;
    }


    public function subject($subject="")
    {
        if ($subject != "") {
            $this->subject = $subject;
        }
        return $this->subject;
    }


    public function message($message="")
    {
        if ($message != "") {
            $this->message = $message;
        }
        return $this->message;
    }

    public function textPlain($textPlain="")
    {
        if ($textPlain != "") {
            $this->textPlain = $textPlain;
        }
        return $this->textPlain;
    }

    public function textHtml($textHtml="")
    {
        if ($textHtml != "") {
            $this->textHtml = $textHtml;
        }
        return $this->textHtml;
    }


}