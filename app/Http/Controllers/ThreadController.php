<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attachement;
use App\Models\Thread;
//use App\Models\Thread;
use \App\Services\ThreadService;
use App\Models\Ticket;
use App\Services\MLService;
use Exception;

class ThreadController extends Controller
{
    public function index(Request $request) {

        $showAll = false;
        if (isset($request->showAll) && $request->showAll == "true") {
            $showAll = true;
        }
        
        if (! auth()->user()->can("viewAny", Thread::class)) {
            throw new Exception("Not Authorised");
        }

        $threadBuilder = Thread::with("ticket", "ticket.department")
        ->where("type", "from-client");
 
        if ($showAll) {
            $threadBuilder->whereNot("canned_reply", "__DELETED__");
        } else {
            $threadBuilder->where("canned_reply", "");
        }

        $threads = $threadBuilder->orderBy("id", "DESC")
        ->get();

        return response()->json(
            $threads
        , 200);
    }


    public function deleteCannedReply(Thread $thread) {

        if (! auth()->user()->can("update", $thread)) {
            throw new Exception("Not Authorised");
        } 

        $thread->canned_reply = '__DELETED__';
        $thread->save();

    }

    public function update(Request $request, Thread $thread) {

        if (! auth()->user()->can("update", $thread)) {
            throw new Exception("Not Authorised");
        } 

        $validatedData = $request->validate([
            "message"   => "string|required",
            "cannedReply"   => "string|required"
        ]);

        $thread->canned_reply   = $validatedData["cannedReply"];
        $thread->message        = $validatedData["message"];
        $thread->save();
    }


    public function show(Thread $thread) {

        if (! auth()->user()->can("view", $thread)) {
            throw new Exception("Not Authorised");
        }

        $thread->load("ticket", "ticket.department");

        return response()->json(
            $thread
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
