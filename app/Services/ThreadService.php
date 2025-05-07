<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendMLThread;
use App\Jobs\ThreadReplyCreatedEmail;
use App\Models\Thread;
use App\Models\Ticket;
use App\Models\Attachement;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ThreadService
{

    public function store(
        Ticket $ticket,
        string $type,
        string $message,
        string $randomString,
        bool $skipEmail=false,
        string $cannedReply="",
        bool $isNewTicket=false,
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


                $cannedReplyModel = null;
                if ($isNewTicket) {
                    $cannedReplyService = new CannedReplyService();
                    $cannedReplyModel = $cannedReplyService->find($ticket->department, $intent);
                }
                
                // for now we only auto answer new tickets,
                // not follow up questions
                if ($isNewTicket && $cannedReplyModel != null && $cannedReplyModel->use_ml) {

                    $this->store(
                        $ticket,
                        "to-client",
                        $cannedReplyModel->message."<p><span style=\"font-size:0.75em; color:#6d6d6e;\">Ticket automatically answered by AI</span>",
                        $randomString,
                    );

                    $skipEmail = true;

                    $ticket->status = "closed";
                    $ticket->save();

                } else {

                    // just create an internal note
                    // so we know what the ml suggested
                    $this->store(
                        $ticket,
                        "internal-note",
                        $intent,
                        $randomString,
                        true,
                        ""
                    );
                }
                
            } catch (Exception $e) {
                $intent = "";
                Log::debug("error: ".$e->getMessage());
            }

        }

        if ($skipEmail == false) {
            ThreadReplyCreatedEmail::dispatch($thread);
        }

        return $thread->load("attachement");

    }


}
