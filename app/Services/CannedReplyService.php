<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use App\Models\CannedReply;

use Ramsey\Uuid\Type\Integer;
use Illuminate\Support\Str;

class CannedReplyService
{
    public function index()
    {

        $user = auth()->user();

        if ($user->level < 10) {
            // individual user, can't manage canned replies
            throw new \Exception("Not authorized", 401);

        } else if ($user->level >= 10 && $user->level < 20) {

           // department owner, manage own department canned replies
           $cannedReplies = CannedReply::where(["department_id" => Department::where(["user_id" => intVal($user->id)])->pluck("id")->first()])->with("department")->get();

        } else if ($user->level >= 100) {
            
            // admin 
            $cannedReplies = CannedReply::with("department")->get();
        }



        return $cannedReplies;
    }




    public function store(String $message, String $title, bool $useMl, int $departmentId)
    {
       
        $department = Department::find($departmentId);
        $cannedReply = new CannedReply();

        if ( ! auth()->user()->can("create", [$cannedReply, $department])) {
            throw new \Exception("Not authorize", 401);
        }
 
        $cannedReply->title = $title;
        $cannedReply->slug = Str::slug($title).'['.strtolower(Str::random(16)).']';
        $cannedReply->message = $message;
        $cannedReply->use_ml = $useMl;
        $cannedReply->department_id = $departmentId;

        $cannedReply->save();

        return true;
    }


    public function update(CannedReply $cannedReply, bool $useMl, String $message, string $title)
    {

        if ( ! auth()->user()->can("update", $cannedReply)) {
            throw new \Exception("Not authorize", 401);
        }

        $cannedReply->message   = $message;
        $cannedReply->use_ml    = $useMl;
        $cannedReply->title     = $title;
        $cannedReply->save();

        return true;

    }
}


