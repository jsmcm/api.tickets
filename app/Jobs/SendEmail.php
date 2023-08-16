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

use Illuminate\Support\Facades\Log;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Department $department, private string $email, private string $subject, private int $ticketId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Log::write("debug", "in SendEmail...(".$this->department->host_name.")");
        Mail::to($this->email)->send(new TicketCreated([
            "subject"       => $this->subject,
            "ticketId"      => $this->ticketId,
            "department"    => $this->department
        ]));

        
    }
}
