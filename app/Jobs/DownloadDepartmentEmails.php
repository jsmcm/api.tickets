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

class DownloadDepartmentEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Department $department)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Log::debug("downloading emails for dept: ".$this->department->department);
        Log::debug("id: ".$this->department->id);
        Log::debug("user_id: ".$this->department->user_id);
        Log::debug("signature: ".$this->department->signature);
        Log::debug("mail_host: ".$this->department->mail_host);
        Log::debug("pop_port: ".$this->department->pop_port);
        Log::debug("smtp_port: ".$this->department->smtp_port);
        Log::debug("mail_username: ".$this->department->mail_username);
        Log::debug("mail_password: ".$this->department->mail_password);
        Log::debug("email_address: ".$this->department->email_address);

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
