<?php

namespace App\Mail;

//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class TicketCreated extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private array $config)
    {
        //
    }

    public function build()
    {

        Log::write("debug", "in build...");

        Log::write("debug", "host: ".$this->config["department"]->mail_host);
        Log::write("debug", "user: ".$this->config["department"]->mail_username);
        Log::write("debug", "pass: ".$this->config["department"]->mail_password);
        Log::write("debug", "port: ".$this->config["department"]->smtp_port);

        $factory = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory();

        $transport = $factory->create(new \Symfony\Component\Mailer\Transport\Dsn(
            "smtp",
            $this->config["department"]->mail_host,
            $this->config["department"]->mail_username,
            $this->config["department"]->mail_password,
            $this->config["department"]->smtp_port,
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
        Log::write("debug", "in envelope...");
        Log::write("debug", "from: ".$this->config["department"]->email_address);

        return new Envelope(
            subject: "[#".str_pad(dechex($this->config["ticketId"]), 5, "0", STR_PAD_LEFT)."] Ticket Created",
            from: $this->config["department"]->email_address
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket.created',
            text: 'emails.ticket.created-text',
            with: [
                "ticketId"      => "#".str_pad(dechex($this->config["ticketId"]), 5, "0", STR_PAD_LEFT),
                "appName"       => env('APP_NAME'),
                "subject"       => $this->config["subject"]
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
        return [];
    }
}
