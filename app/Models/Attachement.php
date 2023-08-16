<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachement extends Model
{
    use HasFactory;


    function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
    
    function thread()
    {
        return $this->belongsTo(Thread::class);
    }
    
}
