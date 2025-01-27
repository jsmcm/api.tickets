<?php

declare(strict_types=1);

namespace App\Services;

use \App\Models\User;

use \App\Models\Ticket;
use \App\Models\Thread;
use \App\Models\Department;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Jobs\TicketCreatedEmail;

class TicketService
{


    public function search(string $searchTerm)
    {
       
        // https://github.com/spatie/laravel-permission/issues/547
        // // do like this sometime
        // $role = "admin";
        // $users = User::search($search_query)->query(function (Builder $query) use ($role){
        //             $query->whereHas('roles', function (Builder $query) use ($role){
        //                     $query->where('name', $role);
        //             });
        // })->paginate(10)

        $user = auth()->user();
        
        $returnValue = null;

        $tickets = Ticket::search(filter_var($searchTerm, FILTER_UNSAFE_RAW))
            ->get();

        $threadTickets = Thread::search(filter_var($searchTerm, FILTER_UNSAFE_RAW))
            ->get()
            ->unique("ticket_id")
            ->load("ticket")
            ->pluck("ticket");

            // Ticket::with("user")->with("department")->withCount("attachement")->with("thread")->get()->pull(1);


   
        $merged = $tickets->merge($threadTickets)
            ->unique()
            ->sort();
        
        $merged->load("user")->load("department")->loadCount("attachement")->load("thread");
        
        if ($user->level > 49) {
            // admins, can see all
            $returnValue = $merged;
        } else if ($user->level > 9 && $user->level < 50) {
            // filter out only those that belong to this user's department
            $departments = Department::where([
                "user_id" => $user->id
            ])
            ->pluck("id");

            $returnValue = $merged->filter( function ($value, $key) use ($departments) { 
                return $departments->id == $value->department_id;
            });    

        } else if ($user->level < 10) {
            // end user
            $returnValue = $merged->filter( function ($value, $key) use ($user) { 
                return $user->id == $value->user_id;
            });
        }

        return $returnValue;
        
    }









    public function userSearch(string $searchTerm)
    {
       
        $user = auth()->user();
        
        // end users cannot search by email, they can
        // only view their own tickets;
        if ($user->level < 10) {
            return null;
        }

   
        $userIds = User::where("email", "LIKE", "%".$searchTerm."%")
            ->orWhere("name", "LIKE", "%".$searchTerm."%")
            ->pluck("id");

        $tickets = Ticket::whereIn("user_id", $userIds);


        if ($user->level > 9 && $user->level < 50) {
            // filter out only those that belong to this user's department
            $departments = Department::where([
                "user_id" => $user->id
            ])
            ->pluck("id");

            $tickets->whereIn("department_id", $departments);  
        }

        $tickets->with("user")
            ->with("department")
            ->withCount("attachement")
            ->with("thread");

        return $tickets->get();
        
    }





    public function store(
        string $subject,
        int $departmentId,
        string $ip,
        string $priority,
        string $email,
        string $firstName
    )
    {
        
        if ($departmentId <= 0) {
            throw new \Exception("Department Id not set", 1600001);
        }
        
        if ($email == "") {
            throw new \Exception("Email not set", 1600002);
        }

        if ($priority == "") {
            $priority = "normal";
        }

    
    
        $user = User::where([
            "email" => $email
        ])->first();

        if ($user == null) {
            $user           = new User();
            $user->level    = 1;
            $user->name     = $firstName;
            $user->email    = $email;
            $user->password = Hash::make(date("Y-m-d H:i:s").mt_rand(10000, 99999).mt_rand(10000, 99999));

            $user->save();
        }
    

        
        $ticket = new Ticket();

        $ticket->department_id  = $departmentId;
        $ticket->user_id        = $user->id;
        $ticket->subject        = $subject;
        $ticket->ip             = $ip;
        $ticket->folder_hash    = date("YmdHis")."_".Str::random(10);
        $ticket->priority       = $priority;

        $ticket->save();

        TicketCreatedEmail::dispatch(Department::find($departmentId), $email, $subject, $ticket->id);

        return $ticket;

    }


    public function merge(Ticket $gainingTicket, Ticket $losingTicket)
    {

        if ($gainingTicket->user_id != $losingTicket->user_id) {
            throw new \Exception("Merge ticket does not belong to merging ticket user");
        }

        if ($losingTicket->deleted_at != null) {
            throw new \Exception("Merging ticket deleted (possibly already merged)");
        }


        $gainingTicket->subject     = $gainingTicket->subject." (".$losingTicket->subject.")";
        $losingTicket->deleted_at  = date("Y-m-d H:i:s");
        $losingTicket->subject     = $losingTicket->subject." (merged with ".$gainingTicket->id.")";

        $gainingTicket->save();
        $losingTicket->save();

        $threads = $losingTicket->thread;

        foreach ($threads as $thread) {
            $thread->ticket_id = $gainingTicket->id;
            $thread->save();
        }

        return true;

    }
    
    

}
