<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use App\Services\SoftSmartMailer\MailerTransport;
use Illuminate\Support\Facades\DB;


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
            DB::statement("PRAGMA journal_mode = WAL");
            DB::statement("PRAGMA cache_size = 2000");
            DB::statement("PRAGMA synchronous = NORMAL");
    }
}
