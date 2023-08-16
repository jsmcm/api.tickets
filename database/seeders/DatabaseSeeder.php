<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
            "level"     => 1,
            "name"      => "John McMurray",
            "email"     => "john@softsmart.co.za",
            "password"  => "$2y$10$7z4pEhSKCyEQWxLc1G7uUOiZ0jULKyS8JmnclHg38XsNZZbMsiSJm",
        ]);

        \App\Models\User::create([
            "level"     => 1,
            "name"      => "John FK McMurray",
            "email"     => "john@fluffykids.co.za",
            "password"  => "$2y$10$7z4pEhSKCyEQWxLc1G7uUOiZ0jULKyS8JmnclHg38XsNZZbMsiSJm",
        ]);

        /**
         * Departments
         */
        \App\Models\Department::create([
            "department"    => "Support",
            "user_id"       => 1,
            "signature"     => "Thanks, John at softsmart",
            "mail_host"     => "mail.softsmart.co.za",
            "pop_port"      => 143,
            "smtp_port"     => 587,
            "mail_username" => "test@softsmart.co.za",
            "mail_password" => "M4thewMc05",
            "email_address" => "test@softsmart.co.za",
            "deleted"       => false,
        ]);



        \App\Models\Department::create([
            "department"    => "Support",
            "user_id"       => 2,
            "signature"     => "Thank you, FluffyKids",
            "mail_host"     => "mail.fluffykids.co.za",
            "pop_port"      => 143,
            "smtp_port"     => 587,
            "mail_username" => "test@fluffykids.co.za",
            "mail_password" => "M4thewMc05",
            "email_address" => "test@fluffykids.co.za",
            "deleted"       => false,
        ]);



        \App\Models\Department::create([
            "department"    => "OLD Support",
            "user_id"       => 2,
            "signature"     => "Thank you, FluffyKids",
            "mail_host"     => "mail.fluffykids.co.za",
            "pop_port"      => 143,
            "smtp_port"     => 587,
            "mail_username" => "oldtest@fluffykids.co.za",
            "mail_password" => "M4thewMc05",
            "email_address" => "oldtest@fluffykids.co.za",
            "deleted"       => true,
        ]);








        /**
         * Tickets
         */
        \App\Models\Ticket::create([
            "department_id" => 1,
            "date_opened"   => \Carbon\Carbon::now(),
            "user_id"       => 1,
            "subject"       => "How to vote",
            "ip"            => "1.1.1.1",
            "folder_hash"   => mt_rand(10000, 99999),
            "intent"        => "how-to-vote",
            "priority"      => "normal",
        ]);
    
        \App\Models\Thread::create([
            "ticket_id" => 1,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "from-client",
            "message"   => "Hello, I need help how to vote please",
        ]);
            
        \App\Models\Thread::create([
            "ticket_id" => 1,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "internal-note",
            "message"   => "Make this a little clearer",
        ]);
        

        \App\Models\Thread::create([
            "ticket_id" => 1,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "to-client",
            "message"   => "Hello, here is the info on how to vote",
        ]);
        

        \App\Models\Thread::create([
            "ticket_id" => 1,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "from-client",
            "message"   => "Thank you",
        ]);
        

        \App\Models\Attachement::create([
            "ticket_id" => 1,
            "thread_id" => 1,
            "file_url"  => 'https://softsmart.co.za/file1.txt'
        ]);

        \App\Models\Attachement::create([
            "ticket_id" => 1,
            "thread_id" => 1,
            "file_url"  => 'https://softsmart.co.za/file2.txt'
        ]);



        \App\Models\Attachement::create([
            "ticket_id" => 1,
            "thread_id" => 3,
            "file_url"  => 'https://softsmart.co.za/file3.txt'
        ]);

        \App\Models\Attachement::create([
            "ticket_id" => 1,
            "thread_id" => 3,
            "file_url"  => 'https://softsmart.co.za/file4.txt'
        ]);

        \App\Models\Attachement::create([
            "ticket_id" => 1,
            "thread_id" => 3,
            "file_url"  => 'https://softsmart.co.za/file5.txt'
        ]);








        \App\Models\Ticket::create([
            "department_id" => 2,
            "date_opened"   => \Carbon\Carbon::now(),
            "user_id"       => 2,
            "subject"       => "How to enter",
            "ip"            => "1.1.1.2",
            "folder_hash"   => mt_rand(10000, 99999),
            "intent"        => "",
            "priority"      => "low",
        ]);


        \App\Models\Thread::create([
            "ticket_id" => 2,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "from-client",
            "message"   => "How can I enter please?",
        ]);
        


        \App\Models\Thread::create([
            "ticket_id" => 2,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "to-client",
            "message"   => "You can fill in our entry form",
        ]);
        




        \App\Models\Ticket::create([
            "department_id" => 1,
            "date_opened"   => \Carbon\Carbon::now(),
            "user_id"       => 1,
            "subject"       => "Missing Votes",
            "ip"            => "1.1.1.3",
            "folder_hash"   => mt_rand(10000, 99999),
            "intent"        => "",
            "priority"      => "high",
        ]);



        \App\Models\Thread::create([
            "ticket_id" => 3,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "from-client",
            "message"   => "I've voted but I don't see those votes, what's going on?",
        ]);
        


        \App\Models\Thread::create([
            "ticket_id" => 3,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "to-client",
            "message"   => "Stupid cell phone networks",
        ]);
        





        \App\Models\Ticket::create([
            "department_id" => 1,
            "date_opened"   => \Carbon\Carbon::now(),
            "user_id"       => 2,
            "subject"       => "No Photo",
            "ip"            => "1.1.1.4",
            "folder_hash"   => mt_rand(10000, 99999),
            "intent"        => "",
            "priority"      => "normal",
        ]);



        \App\Models\Thread::create([
            "ticket_id" => 4,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "from-client",
            "message"   => "How can I add a photo to my entry?",
        ]);
        

        \App\Models\Thread::create([
            "ticket_id" => 4,
            "date"      => \Carbon\Carbon::now(),
            "type"      => "to-client",
            "message"   => "You can log in to edit your entry",
        ]);
        






    }
}
