<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DepartmentService
{

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
        string $apiToken

    )
    {

        if ( ! auth()->user()->can("update", $department)) {
            throw new \Exception("Not authorize", 401);
        }

	Log::debug("dpeartment: ".$department->department
		."\r\nlogo: ".$logoUrl
		."\r\nsig: ".$signature
		."\r\nmail_host: ".$mailHost
		."\r\npop_port: ".$popPort
		."\r\nsmttp: ".$smtpPort
		."\r\nmailUser: ".$mailUsername
		."\r\npass: ".$mailPassword
		."\r\nemail: ".$departmentEmail
		."\r\napiurl: ".$apiBaseUrl
		."\r\ntoken: ".$apiToken
	);

        $department->department     = $departmentTitle;
        $department->logo_url       = $logoUrl;
        $department->signature      = $signature;
        $department->mail_host      = $mailHost;
        $department->pop_port       = $popPort;
        $department->smtp_port      = $smtpPort;
        $department->mail_username  = $mailUsername;
        $department->mail_password  = $mailPassword;
        $department->email_address  = $departmentEmail;
        $department->api_token      = $apiBaseUrl;
        $department->api_base_url   = $apiToken;


        $department->save();

        return true;

    }
}


