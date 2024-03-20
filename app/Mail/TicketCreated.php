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

        /*
        future dev, this is a problem (not really, but kindof).
        Originally emails would be sent with a subject like this: '[#04b4c] Ticket Created"
        where the number is square brackets is the hex representation of the ticket id.
        The problem is with the subject line below the closing ] causes the subject line
        to cache to the first instance of it, so for instance if it sent out
        '[#00302] Ticket Created', it would keep sending that over and over.

        I'm not sure if this 'caching' persists across email addresses. Interestingly if I left 
        off the closing ] it worked, eg: '[#00302 Ticket Created'.

        My guess is that because ] is a special character in regexes something is happening on 
        that front, but for now I'm simply changing to ()
        */
        return new Envelope(
            subject: "(#".str_pad(dechex($this->config["ticketId"]), 5, "0", STR_PAD_LEFT).") Ticket Created",
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
