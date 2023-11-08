<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory;

    use SoftDeletes; // use the trait

    protected $hidden = [
        'mail_host',
        'pop_port',
        'smtp_port',
        'mail_username',
        'mail_password',
        'email_address',
    ];

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }


    public function cannedReply()
    {
        return $this->hasMany(CannedReply::class);
    }

    
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
