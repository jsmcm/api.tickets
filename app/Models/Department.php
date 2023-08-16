<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

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
}
