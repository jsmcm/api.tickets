<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\ThreadReplyCreated;
use App\Models\Thread;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class ThreadReplyCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Thread $thread)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        Log::debug("in ThreadReplyCreatedEmail job");
        
        $ticket = $this->thread->ticket;
        $user   = $ticket->user;
        
        Log::debug("Ticket: ".$ticket->id);
        Log::debug("User: ".$user->email);
        

        Mail::to($user->email)->send(
            new ThreadReplyCreated($this->thread)
        );

        Log::debug("job done");
    }
}
