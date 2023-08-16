<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Thread;
use App\Models\Ticket;
use Exception;

class ThreadController extends Controller
{


    public function post(Ticket $ticket, Request $request)
    {

        $type = "";
        if (isset($request->type)) {
            $type = filter_var($request->type, FILTER_UNSAFE_RAW);
        }
        if ($type == "") {
            throw new Exception("Thread type not set", 1600001);
        }

        $message = "";
        if (isset($request->message)) {
            $message = filter_var($request->message, FILTER_UNSAFE_RAW);
        }
        if ($message == "") {
            throw new Exception("Message is blank", 1600002);
        }
        
        $threadId = $this->store($ticket->id, $type, $message);
        if ($threadId) {
            return response()->json(
                [
                    "status" => "success", 
                    "thread_id" => $threadId
                ], 200
            );
        }

    }



    public function store(
        int $ticketId,
        string $type,
        string $message
    )
    {

        if ($ticketId == 0) {
            throw new Exception("Ticket Id not set", 1500001);
        }

        if ($message == "") {
            throw new Exception("Message not set", 1500002);
        }

        $thread = new Thread();
        $thread->ticket_id = $ticketId;
        $thread->type = $type;
        $thread->message = $message;
        
        $thread->save();

        return $thread->id;

    }

}
