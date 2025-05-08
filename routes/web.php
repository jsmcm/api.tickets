<?php
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Jobs\TicketCreatedEmail;
use App\Mail\TicketCreated;
use App\Models\Department;
use Illuminate\Support\Facades\Log;
use App\Services\MailDownloader\Download;
use Illuminate\Support\Facades\Cache;

use App\Jobs\DownloadEmails;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get("/run", function () {
    
    DownloadEmails::dispatch();

});

Route::get('/', function () {
    return "<a href='https://softsmart.co.za'>SoftSmart.co.za</a>";
});

Route::get("/pragma", function() {

        $mode = \DB::select("PRAGMA journal_mode");
        $cache = \DB::select("PRAGMA cache_size");
        $sync = \DB::select("PRAGMA synchronous");
        return [$mode, $cache, $sync];
});
