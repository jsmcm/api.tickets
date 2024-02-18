<?php

namespace App\Jobs;

use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\MailDownloader\Download;

use Illuminate\Support\Facades\Log;

class DownloadDepartmentEmails implements ShouldQueue //, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    // public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(private Department $department)
    {
        //
    }

    // public function uniqueId()
    // {
        // return $this->department->id;
    // }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //


        Log::debug("downloading emails for dept: ".$this->department->department);

        $download = new Download(
            $this->department->mail_username,
            $this->department->mail_password,
            \App\Jobs\MakeTicketFromEmail::class, 
            $this->department->mail_host, 
            $this->department->pop_port,
            "imap"
        );

        $download->download();

    }
}
