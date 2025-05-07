<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CannedReply extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            "use_ml" => "boolean"
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
