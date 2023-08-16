<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Exception;

class Ticket extends Model
{
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function thread()
    {
        return $this->hasMany(Thread::class);
    }

    public function attachement()
    {
        return $this->hasMany(Attachement::class);
    }


    public function merge(Ticket $ticket)
    {

        if ($ticket->user_id != $this->user_id) {
            throw new Exception("Merge ticket does not belong to merging ticket user");
        }

        if ($this->deleted == true) {
            throw new Exception("Merging ticket deleted (possibly already merged)");
        }


        $ticket->subject = $ticket->subject." (".$this->subject.")";
        $this->deleted = true;
        $this->subject = $this->subject." (merged with ".$ticket->id.")";

        $this->save();
        $ticket->save();

        $threads = $this->thread;

        foreach ($threads as $thread) {
            $thread->ticket_id = $ticket->id;
            $thread->save();
        }

        return true;

    }


}
