<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attachement;
use App\Models\Thread;
//use App\Models\Thread;
use \App\Services\ThreadService;
use App\Models\Ticket;
use Exception;

class ThreadController extends Controller
{


    public function index() {

        if (! auth()->user()->can("viewAny", Thread::class)) {
            throw new Exception("Not Authorised");
        }

        $threads = Thread::with("ticket", "ticket.department")
        ->where("type", "from-client")
        ->whereNot("canned_reply", "__DELETED__")
        ->orderBy("id", "DESC")
        ->get();

        return response()->json(
            $threads
        , 200);
    }


    public function store(Ticket $ticket, Request $request)
    {

        $validatedData = $request->validate([
            "type"          => "required|string",
            "message"       => "required|string",
            "randomString"  => "required|string",
            "skipEmail"     => "boolean",
            "cannedReply"   => "string|nullable",
        ]);


        $threadService = new ThreadService();

        try {
            
            $thread = $threadService->store(
                $ticket,
                $validatedData["type"],
                $validatedData["message"],
                $validatedData["randomString"],
                $validatedData["skipEmail"],
                $validatedData["cannedReply"] ?? "",
            );

        } catch (\Exception $e) {
            return response()->json(
                [
                    "status" => "error", 
                    "message" => $e->getMessage()
                ], 500
            );   
        }


        if ($thread != null) {
            return response()->json(
                [
                    "status" => "success", 
                    "thread" => $thread
                ], 200
            );
        }

    }


}
