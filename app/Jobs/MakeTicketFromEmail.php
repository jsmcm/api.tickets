<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\TicketService;
use Illuminate\Support\Facades\Log;

use App\Services\MailDownloader\Mail;
use App\Services\ThreadService;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\Attachement;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;

class MakeTicketFromEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Mail $mail)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //

        $sentTo = "";
        if (isset($this->mail->headers()["Envelope-to"])) {
            $sentTo = $this->mail->headers()["Envelope-to"];
        } else if (isset($this->mail->headers()["Delivered-To"])) {
            $sentTo = $this->mail->headers()["Delivered-To"];
        }

        $department = Department::where(["email_address" => $sentTo])->first();


        $ticket = null;

        $isNewTicket = true;
        // Log::debug("Subject is: " . $this->mail->subject());

        if(preg_match ("/[[][#][a-fA-F0-9]{5,15}[]]/",$this->mail->subject(), $regs)) {
            // Log::debug("regs are: ".print_r($regs, true));
            
            // [#00035]
            $ticketId = hexdec(str_replace([
                "[",
                "#",
                "]"
            ], '', $regs[0]));

            // Log::debug("ticketId: ".$ticketId);


            $ticket = Ticket::find($ticketId);

            // Log::debug("email is from: ".$this->mail->fromAddress());

            // Log::debug("ticket email : ".$ticket->user->email);

            if ($this->mail->fromAddress() != $ticket->user->email) {
                //Log::debug("ticket does not belong to this email....");
                DifferentEmailAddressEmail::dispatch($ticket->department, $this->mail->fromAddress(), $this->mail->subject(), $ticket->id);
                return;
                //throw new \Exception("Email received for ticket from different email address. Expected from: ".$ticket->user->email." but received from: ".$this->mail->fromAddress());
            }

            $isNewTicket = false;
        }

        if ($isNewTicket) {
            $ticketService = new TicketService();

            $ticket = $ticketService->store(
                $this->mail->subject(),
                $department->id,
                $this->mail->ips()[0],
                "normal",
                $this->mail->fromAddress(),
                $this->mail->fromName()
            );
        } else {
            if ($ticket->status == "closed") {
                $ticket->status = "open";
                $ticket->save();
            }
        }


        $threadService = new ThreadService();

        $thread = $threadService->store(
            $ticket,
            "from-client",
            $this->mail->message(),
            Str::random(32),
            true
        );


        // Log::debug("mail: ".print_r($this->mail, true));


        $attachments = $this->mail->attachments();

        // Log::debug("attachments: ".count($attachments));

        if (!empty($attachments)) {
            
            foreach ($attachments as $attachment) {

                // Log::debug("attachment: ".print_r($attachment, true));

                $randomString = date("Ydm_His")."_".Str::random(48);
                $path = "attachements/temp/".$randomString."_".$attachment->id."_".$attachment->name;

                // Log::debug("move: ".$attachment->path." to ".$path);
               Storage::disk("s3")->put(
                    $path,
                    file_get_contents($attachment->path),
                    "public"
                );

                $attachmentModel = new Attachement();
                $attachmentModel->ticket_id = $ticketId;
                $attachmentModel->thread_id = $thread->id;
                $attachmentModel->random_string = $randomString;
                $attachmentModel->uuid = $attachment->id;
                $attachmentModel->file_url = "https://".config("filesystems.disks.s3.bucket").".s3.".config("filesystems.disks.s3.region").".amazonaws.com/".$path;
                
                $attachmentModel->save();
            }
        }




    }
}
