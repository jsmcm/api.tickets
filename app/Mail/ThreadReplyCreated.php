<?php

namespace App\Mail;

//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Thread;


class ThreadReplyCreated extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private Thread $thread)
    {
        //
    }

    public function build()
    {

        // Log::write("debug", "in build...");

        // Log::write("debug", "host: ".$this->config["department"]->mail_host);
        // Log::write("debug", "user: ".$this->config["department"]->mail_username);
        // Log::write("debug", "pass: ".$this->config["department"]->mail_password);
        // Log::write("debug", "port: ".$this->config["department"]->smtp_port);

        $factory = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory();

        $ticket = $this->thread->ticket;
        $department = $ticket->department;

        $transport = $factory->create(new \Symfony\Component\Mailer\Transport\Dsn(
            "smtp",
            $department->mail_host,
            $department->mail_username,
            $department->mail_password,
            $department->smtp_port,
            [
                'encryption' => null, // Enable STARTTLS encryption
            ]
        ));
        Mail::setSymfonyTransport($transport);

    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Log::write("debug", "in envelope...");
        // Log::write("debug", "from: ".$this->config["department"]->email_address);
        $ticket = $this->thread->ticket;
        $department = $ticket->department;

        return new Envelope(
            subject: "[#".str_pad(dechex($ticket->id), 5, "0", STR_PAD_LEFT)."] New Reply on Ticket",
            from: $department->email_address
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $ticket = $this->thread->ticket;
        $department = $ticket->department;

        return new Content(
            view: 'emails.thread.created',
            text: 'emails.thread.created-text',
            with: [
                "ticketId"      => "#".str_pad(dechex($ticket->id), 5, "0", STR_PAD_LEFT),
                "department"    => $department->department,
                "subject"       => $ticket->subject,
                "signature"     => $department->signature,
                "logo"          => $department->logo_url,
                "messaged"       => $this->thread->message
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {


        $threadAttachements = $this->thread->attachement;

        $attachments = [];

        foreach ($threadAttachements as $attachment) {

            $fileUrl = $attachment->file_url;

            // remove the awstorage url part
            $fileUrl = substr($fileUrl, strpos($fileUrl, "/", 10));

            // remove our internal unique ids, etc, leaving only the actual filename part
            $fileName = substr($fileUrl, strpos($fileUrl, "_",  strpos($fileUrl, "_",  strpos($fileUrl, "_") + 1) + 1) + 1);

            $attachments[] = attachment::fromStorage($fileUrl)
                                ->as($fileName);
        }

        return $attachments;
    }
}
