<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use App\Services\SoftSmartMailer\MailerTransport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        Mail::extend("softsmartmailer", function () {

            $config = [
                "end_point" => config("mail.mailers.softsmartmailer.end_point"),
                "mail_name" => config("mail.mailers.softsmartmailer.mail_name"),
                "mailer"    => config("mail.mailers.softsmartmailer.mailer"),
                "api_key"   => config("mail.mailers.softsmartmailer.api_key"),
            ];
            return new MailerTransport($config);
        });

    }
}
