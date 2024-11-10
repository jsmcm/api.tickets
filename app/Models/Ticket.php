<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

use Exception;

class Ticket extends Model
{
    use HasFactory, SoftDeletes, Searchable;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function thread()
    {
        return $this->hasMany(Thread::class);
    }

    public function attachement()
    {
        return $this->hasMany(Attachement::class);
    }

    public function toSearchableArray()
    {
        return [
            "subject"   => $this->subject
        ];
    }

}
