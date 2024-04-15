<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ThreadReplyCreatedEmail;
use App\Models\Thread;
use App\Models\Ticket;
use App\Models\Attachement;

use Illuminate\Support\Facades\Log;

class ThreadService
{

    public function store(
        Ticket $ticket,
        string $type,
        string $message,
        string $randomString,
        bool $skipEmail
    )
    {

        if ($type == "") {
            throw new \Exception("Thread type not set", 1600001);
        }

        if ($message == "") {
            throw new \Exception("Message is blank", 1600002);
        }


        Log::debug("Saving new thread....");

        $thread = new Thread();
        $thread->ticket_id = $ticket->id;
        $thread->type = $type;
        $thread->message = $message;
        
        $thread->save();

        Log::debug("thread id: ".$thread->id." saved");

        if ($thread->id) {

            Log::debug("Assigning attachments...");
            // update attachements
            Attachement::where(["random_string" => $randomString])
                ->update([
                    "ticket_id" => $ticket->id,
                    "thread_id" => $thread->id
                ]);

                Log::debug("Attachements assigned..");

        }

        if ($skipEmail == false) {
            Log::debug("Creating job ThreadReplyCreatedEmail");
            ThreadReplyCreatedEmail::dispatch($thread);
        }

        Log::debug("done, returinging");
        return $thread->load("attachement");

    }


}
