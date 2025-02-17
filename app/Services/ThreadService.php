<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ThreadReplyCreatedEmail;
use App\Models\Thread;
use App\Models\Ticket;
use App\Models\Attachement;
use Exception;

class ThreadService
{

    public function store(
        Ticket $ticket,
        string $type,
        string $message,
        string $randomString,
        bool $skipEmail,
        string $cannedReply
    ): Thread
    {

        if ($type == "") {
            throw new \Exception("Thread type not set", 1600001);
        }

        if ($message == "") {
            throw new \Exception("Message is blank", 1600002);
        }

        $thread = new Thread();
        $thread->ticket_id = $ticket->id;
        $thread->type = $type;
        $thread->message = $message;
        $thread->canned_reply = $cannedReply;
        
        $thread->save();

        if ($thread->id) {

            // update attachements
            Attachement::where(["random_string" => $randomString])
                ->update([
                    "ticket_id" => $ticket->id,
                    "thread_id" => $thread->id
                ]);

        }

        if ($type == "from-client") {
            $mlService = new MLService();

            try {
                $intent = $mlService->getIntent($message);

                $this->store(
                    $ticket,
                    "internal-note",
                    $intent,
                    $randomString,
                    true,
                    ""
                );

            } catch (Exception $e) {
                $intent = "";
            }

        }

        if ($skipEmail == false) {
            ThreadReplyCreatedEmail::dispatch($thread);
        }

        return $thread->load("attachement");

    }


}
