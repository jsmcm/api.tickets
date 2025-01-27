<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Department;
use App\Mail\TicketCreated;
use Illuminate\Support\Facades\Mail;

class TicketCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Department $department, public string $email, public string $subject, public int $ticketId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        Mail::to($this->email)->send(new TicketCreated([
            "subject"       => $this->subject,
            "ticketId"      => $this->ticketId,
            "department"    => $this->department
        ]));

    }

}
