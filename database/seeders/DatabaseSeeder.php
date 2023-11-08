<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use DateTime;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //\App\Models\User::factory(10)->create();

        /**
         * Users
         */
        \App\Models\User::create([
            "level"     => 100,
            "name"      => "John McMurray",
            "email"     => "john@softsmart.co.za",
            "password"  => Hash::make("ffxefc1e")
        ]);



        \App\Models\User::create([
            "level"     => 10,
            "name"      => "John RFTAGS",
            "email"     => "john@rftags.co.za",
            "password"  => Hash::make("ffxefc1e")
        ]);



        \App\Models\User::create([
            "level"     => 10,
            "name"      => "John Pricedrop",
            "email"     => "john@pricedrop.co.za",
            "password"  => Hash::make("ffxefc1e")
        ]);





        \App\Models\User::create([
            "level"     => 1,
            "name"      => "Pricedrop Client",
            "email"     => "pricedrop@client.co.za",
            "password"  => Hash::make("ffxefc1e")
        ]);



        \App\Models\User::create([
            "level"     => 1,
            "name"      => "rftags Client",
            "email"     => "rftags@client.co.za",
            "password"  => Hash::make("ffxefc1e")
        ]);






        /**
         * Departments
         */
        \App\Models\Department::create([
            "department"    => "Pricedrop Support",
            "user_id"       => 3,
            "signature"     => "Thanks, John at pricedrop",
            "mail_host"     => "mail.softsmart.co.za",
            "pop_port"      => 143,
            "smtp_port"     => 587,
            "mail_username" => "test@softsmart.co.za",
            "mail_password" => "M4thewMc05",
            "email_address" => "test@softsmart.co.za",
        ]);


        \App\Models\CannedReply::create([
            "department_id" => 1,
            "message"       => "You can buy here please...",
            "slug"          => "how-to-buy",
            "title"         => "How to buy?",
            "use_ml"        => true,
        ]);






        \App\Models\Department::create([
            "department"    => "RF Tags Support",
            "user_id"       => 2,
            "signature"     => "Thank you, rftags",
            "mail_host"     => "mail.fluffykids.co.za",
            "pop_port"      => 143,
            "smtp_port"     => 587,
            "mail_username" => "john@fluffykids.co.za",
            "mail_password" => "M4thewMc05",
            "email_address" => "test@fluffykids.co.za",
        ]);


        \App\Models\CannedReply::create([
            "department_id" => 2,
            "message"       => "You can rfid here please...",
            "slug"          => "how-to-rfid",
            "title"         => "How to rfid?",
            "use_ml"        => true,
        ]);





        /**
         * Tickets
         */
        \App\Models\Ticket::create([
            "department_id" => 1,
            "date_opened"   => \Carbon\Carbon::now(),
            "user_id"       => 4,
            "subject"       => "I want discount",
            "ip"            => "1.1.1.1",
            "folder_hash"   => mt_rand(10000, 99999),
            "intent"        => "",
            "priority"      => "normal",
        ]);
    
        \App\Models\Thread::create([
            "ticket_id" => 1,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "from-client",
            "message"   => "Hello, I want discount",
        ]);
            
        \App\Models\Thread::create([
            "ticket_id" => 1,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "internal-note",
            "message"   => "Silly",
        ]);
    





    
         \App\Models\Ticket::create([
            "department_id" => 2,
            "date_opened"   => \Carbon\Carbon::now(),
            "user_id"       => 5,
            "subject"       => "How to rftid",
            "ip"            => "1.1.1.1",
            "folder_hash"   => mt_rand(10000, 99999),
            "intent"        => "how-to-vote",
            "priority"      => "normal",
        ]);
    
        \App\Models\Thread::create([
            "ticket_id" => 2,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "from-client",
            "message"   => "Hello, I need help how to read active tags",
        ]);
            
        \App\Models\Thread::create([
            "ticket_id" => 2,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "internal-note",
            "message"   => "Make this a little clearer",
        ]);
        

        \App\Models\Thread::create([
            "ticket_id" => 2,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "to-client",
            "message"   => "Hello, you buy our reader",
        ]);
                

    }
}
