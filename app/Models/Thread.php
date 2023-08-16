<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;


    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }


    public function attachement()
    {
        return $this->hasMany(Attachement::class);
    }
    
}
