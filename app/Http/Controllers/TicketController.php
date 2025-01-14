<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ban;
use Illuminate\Support\Facades\Gate;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Exception;
use Illuminate\Http\Request;
use App\Policies\TicketPolicy;
use App\Services\DepartmentService;
use App\Services\ThreadService;

class TicketController extends Controller
{
    //
    public function destroy(Ticket $ticket, Request $request)
    {

        try {
            $ticket->delete();
        } catch (Exception $e) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => $e->getMessage()
                ], 500);
        }

        
        if ($request->ban == "true") {
            $banObject = Ban::where("email", $ticket->user->email)->first();

            if (empty($banObject)) {
                $banObject = new Ban();
                $banObject->email = $ticket->user->email;
                $banObject->save();
            }
        }

        return response()->json(
            [
                "status"    => "success",
            ], 200
        );

    }




        /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
        if (isset($request->status)) {
            $ticket->status = filter_var($request->status, FILTER_UNSAFE_RAW);
        }


        try {
            $ticket->save();
        } catch (Exception $e) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => $e->getMessage()
                ], 500);
        }

        return response()->json(
            [
                "status"    => "success",
            ], 200
        );

    }


    public function store(Request $request)
    {


        $validatedData = $request->validate([
            "email"         => "required|email",
            "firstName"     => "required|string",
            "departmentId"  => "required|integer",
            "subject"       => "required|string",
            "priority"      => "required|string"
        ]);


        $ticketService = new TicketService();
        try {
            $ticket = $ticketService->store(
                $validatedData["subject"],
                $validatedData["departmentId"],
                $_SERVER["REMOTE_ADDR"],
                $validatedData["priority"],
                $validatedData["email"],
                $validatedData["firstName"]
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => $e->getMessage()
                ], 500);
        }

        return response()->json(
            [
                "status"    => "success", 
                "data"      => $ticket->id
            ], 200
        );

    }




    public function createTicket(Request $request)
    {

        $validatedData = $request->validate([
            "email"             => "required|email",
            "firstName"         => "required|string",
            "departmentEmail"   => "required|integer",
            "subject"           => "required|string",
            "message"           => "required|string",
        ]);

        $departmentService = new DepartmentService();

        $department = $departmentService->departmentByEmail($validatedData["departmentEmail"]);

        if ($department == false) {
            return response()->json([
                "status"    => "error",
                "message"   => "Department not found"
            ], 404); 
        }


        $ticketService = new TicketService();
        try {
            
            $ticket = $ticketService->store(
                $validatedData["subject"],
                $department->id,
                $_SERVER["REMOTE_ADDR"],
                "normal",
                $validatedData["email"],
                $validatedData["firstName"]
            );

            if ($ticket) {

                $threadService = new ThreadService();

                try {
                    
                    $thread = $threadService->store(
                        $ticket,
                        "from-client",
                        $validatedData["message"],
                        "",
                        false,
                        ""
                    );
        
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            "status" => "error", 
                            "message" => $e->getMessage()
                        ], 500
                    );   
                }

            }


        } catch (Exception $e) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => $e->getMessage()
                ], 500);
        }

        return response()->json(
            [
                "status"    => "success", 
                "data"      => $ticket->id
            ], 200
        );



    }






    public function show(Ticket $ticket)
    {
        
        Gate::authorize("view", $ticket);
    
        $tickets = null;

        if (auth()->user()->can("viewInternalNotes", $ticket)) {
            $tickets = $ticket->load(["thread", "department", "user", "thread.attachement"]);
        } else {
            $tickets = $ticket->load(["thread" => function ($query) {
                $query->excludeInternalNotes();
            }, "user", "department", "thread.attachement"]);
        }

        return response()->json(
            [
                "status" => "success", 
                "data" => $tickets
            ], 200
        );
    }


    public function search(Request $request)
    {
        $ticketService = new TicketService();
        
        return response()->json(
            [
                "status"    => "success", 
                "data"      => $ticketService->search(filter_var($request->search, FILTER_UNSAFE_RAW))
            ], 200
        ); 
    }


    public function userSearch(Request $request)
    {
        $ticketService = new TicketService();
       
        return response()->json(
            [
                "status"    => "success", 
                "data"      => $ticketService->userSearch(filter_var($request->search, FILTER_UNSAFE_RAW))
            ], 200
        ); 
    }




    public function index(Request $request)
    {

        $user = auth()->user();

        if ($user == null) {
            return response()->json([
                    "status" => "error",
                    "data" => "Not authorized, please log in..."
                ]
            , 401);
        }


        $priority = ["high","normal","low"];
        if (isset($request->priority)) {
            if ( in_array($request->priority, ["high", "normal", "low"])) {
                $priority = [$request->priority];
            }
        }

        $tickets = Ticket::whereHas("department")
            ->whereIn("priority", $priority);


        if ($user->level < 10) {
            // individual user, search tickets where userid
            $tickets->where(["user_id" => $user->id]);

        } else if ($user->level >= 10 && $user->level < 20) {

           // department owner, search tickers where departmentid
           $departmentIds = Department::where(["user_id" => intVal($user->id)])->pluck("id");
           $tickets->whereIn("department_id", $departmentIds)->get();
        } else if ($user->level >= 100) {
            
            // admin 
            if (isset($request->user)) {
                // searching a specific user
                $tickets->where(["user_id" => intVal($request->user)]);
            }
        }




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
