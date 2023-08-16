<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ticket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    //

    public function store(
        int $departmentId,
        int $userId,
        string $subject,
        string $ip,
        string $priority
    )
    {
        
        if ($departmentId == 0) {
            throw new Exception("Department Id not set", 1600001);
        }
        
        if ($userId == 0) {
            throw new Exception("User Id not set", 1600002);
        }

        if ($priority == "") {
            $priority = "normal";
        }
        
        $ticket = new Ticket();
        $ticket->department_id = $departmentId;
        $ticket->user_id = $userId;
        $ticket->subject = $subject;
        $ticket->ip = $ip;
        $ticket->folder_hash = date("YmdHis")."_".Str::random(10);
        $ticket->priority = $priority;

        $ticket->save();

        return $ticket->id;

    }




    public function get(Ticket $ticket)
    {
        return response()->json(
            [
                "status" => "success", 
                "data" => $ticket->load("thread", "user", "thread.attachement")
            ], 200
        );
    }



    public function index(Request $request)
    {
        
        // $user = auth()->user();


        $priority = ["high","normal","low"];
        if (isset($request->priority)) {
            if ( in_array($request->priority, ["high", "normal", "low"])) {
                $priority = [$request->priority];
            }
        }

        $tickets = Ticket::whereIn("priority", $priority);



        // $userId = 0;
        // if ($user->level < 10) {
        //     $userId = $user->id;
        // } else if ($user->level >= 100) {
            
        //     if (isset($request->user)) {
        //         $userId = intVal($request->user);
        //     }
        // }


        // if ($userId > 0) {
        //     $tickets->where(["user_id" => $userId]);
        // }


        $status = ["open", "overdue", "answered"];
        if (isset($request->status)) {
            if ( in_array($request->status, ["open", "overdue", "answered", "closed"])) {
                $status = [$request->status];
            }
        }

        $tickets->whereIn("status", $status);


        $departmentId = 0;
        if (isset($request->department)) {
            $departmentId = intVal($request->department);
        }

        if ($departmentId > 0) {
            $tickets->where(["department_id" => $departmentId]);
        }

        $tickets->with("user")
        ->with("department")
        ->withCount("attachement")
        ->with("thread");





        return response()->json(
            [
                "status" => "success",
                "data" => $tickets->get()
            ]
        , 200);

    }
}
