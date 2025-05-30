<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;

use Illuminate\Support\Str;

class DepartmentService
{


    public function departmentByEmail(string $email)
    {

        $department = Department::where([
            "email_address" => $email
        ])->first();


        if (isset($department->id)) {
            return $department;
        }

        return false;

    }

    public function update(
        Department $department,
        string $departmentEmail,
        string $departmentTitle,
        string $signature,
        string $logoUrl,
        string $mailHost,
        string $mailUsername,
        string $mailPassword,
        int $popPort,
        int $smtpPort,
        string $apiBaseUrl,
        string $apiToken,
        bool $deleteAfterFetch
    )
    {

        if ( ! auth()->user()->can("update", $department)) {
            throw new \Exception("Not authorize", 401);
        }


        $department->department         = $departmentTitle;
        $department->logo_url           = $logoUrl;
        $department->signature          = $signature;
        $department->mail_host          = $mailHost;
        $department->pop_port           = $popPort;
        $department->smtp_port          = $smtpPort;
        $department->mail_username      = $mailUsername;
        $department->mail_password      = $mailPassword;
        $department->email_address      = $departmentEmail;
        $department->api_base_url       = $apiBaseUrl;
        $department->api_token          = $apiToken;
        $department->delete_after_fetch = $deleteAfterFetch;

        $department->save();


        return true;

    }
}


