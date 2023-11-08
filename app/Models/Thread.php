<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Thread extends Model
{
    use HasFactory, SoftDeletes, Searchable;


    public function scopeExcludeInternalNotes($query)
    {
        return $query->where("type", "<>", "internal-note");
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function attachement()
    {
        return $this->hasMany(Attachement::class);
    }

    public function toSearchableArray()
    {
        return [
            "message"   => $this->message
        ];
    }
}
