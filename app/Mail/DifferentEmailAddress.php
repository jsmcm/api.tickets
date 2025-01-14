<?php

namespace App\Mail;

//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DifferentEmailAddress extends Mailable
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

        $factory = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory();

        $transport = $factory->create(new \Symfony\Component\Mailer\Transport\Dsn(
            "smtp",
            $this->config["department"]->mail_host,
            $this->config["department"]->mail_username,
            $this->config["department"]->mail_password,
            $this->config["department"]->smtp_port,
            [
		    'encryption' => null, // Enable STARTTLS encryption
		    'verify_peer' => 0
            ]
        ));
        Mail::setSymfonyTransport($transport);

    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {

        return new Envelope(
            subject: "Incorrect Email Address",
            from: $this->config["department"]->email_address
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket.different-email',
            text: 'emails.ticket.different-email-text',
            with: [
                "department"    => $this->config["department"]->department,
                "subject"       => $this->config["subject"],
                "signature"     => $this->config["department"]->signature,
                "logo"          => $this->config["department"]->logo_url,
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
