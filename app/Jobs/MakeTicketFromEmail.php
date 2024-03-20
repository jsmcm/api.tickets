<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\TicketService;
// use Illuminate\Support\Facades\Log;

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
    public function __construct(private Array $mail)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $department = Department::where(["email_address" => $this->mail["sentTo"]])->first();

        $ticket = null;

        $isNewTicket = true;

        if(preg_match ("/[(][#][a-fA-F0-9]{5,15}[)]/",$this->mail["subject"], $regs)) {
            
            // [#00035]
            $ticketId = hexdec(str_replace([
                "(",
                "#",
                ")"
            ], '', $regs[0]));


            $ticket = Ticket::find($ticketId);


            if ($ticket !== null) {

                if ($this->mail["fromAddress"] != $ticket->user->email) {
                    // Log::debug("ticket does not belong to this email....");
                    DifferentEmailAddressEmail::dispatch($ticket->department, $this->mail["fromAddress"], $this->mail["subject"], $ticket->id);
                    return;
                    //throw new \Exception("Email received for ticket from different email address. Expected from: ".$ticket->user->email." but received from: ".$this->mail->fromAddress());
                }

                $isNewTicket = false;
            }
        }

        if ($isNewTicket) {
            $ticketService = new TicketService();

            $ticket = $ticketService->store(
                $this->mail["subject"],
                $department->id,
                $this->mail["ip"],
                "normal",
                $this->mail["fromAddress"],
                $this->mail["fromName"]
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
            $this->mail["message"],
            Str::random(32),
            true
        );



        $attachments = $this->mail["attachments"];

        if (!empty($attachments)) {
            
            foreach ($attachments as $attachment) {

                $attachmentModel                = new Attachement();
                $attachmentModel->ticket_id     = $ticket->id;
                $attachmentModel->thread_id     = $thread->id;
                $attachmentModel->random_string = $attachment->randomString;
                $attachmentModel->uuid          = $attachment->id;
                $attachmentModel->file_url      = "https://".config("filesystems.disks.s3_file_storage.bucket").".s3.".config("filesystems.disks.s3_file_storage.region").".amazonaws.com/".$attachment->path;
                
                $attachmentModel->save();
            }
        }


    }

}
