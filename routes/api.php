<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Models\Ticket;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::post("/ticket/thread/{ticket}", "App\Http\Controllers\ThreadController@post");


/**
 * Merge a ticket
 */
Route::get("/ticket/merge/{ticket}", function(Ticket $ticket, Request $request) {

    
    $mergeWith = Ticket::find($request->ticket_id);

    if ($mergeWith == null) {
        return response()->json(["status" => "failed", "data" => "Merge ticket does not exist"], 500);
    }

    try {
        if ($ticket->merge($mergeWith)) {
            return response()->json(["status" => "success", "data" => "ticket merged"], 200);
        }
    } catch (Exception $e) {
        return response()->json(["status" => "failed", "data" => $e->getMessage()], 401);
    }

    
});




/**
 * Create a new ticket
 */
Route::post("/ticket", function(Request $request) {

    $email = "";
    if (isset($request->email)) {
        $email = filter_var($request->email, FILTER_SANITIZE_EMAIL);
    }

    $user = User::where(["email" => $email])->first();
    if ($user == null) {
        $firstName = "";
        if (isset($request->firstName)) {
            $firstName = filter_var($request->firstName, FILTER_UNSAFE_RAW);
        }


        $user = new User();
        $user->level = 1;
        $user->name = $firstName;
        $user->email = $email;
        $user->password = Hash::make(date("Y-m-d H:i:s").mt_rand(10000, 99999).mt_rand(10000, 99999));
        $user->save();
    }

    $departmentId = 0;
    if (isset($request->departmentId)) {
        $departmentId = intVal($request->departmentId);
    }


    $subject = "";
    if (isset($request->subject)) {
        $subject = filter_var($request->subject, FILTER_UNSAFE_RAW);
    }

    
    $priority = "";
    if (isset($request->priority)) {
        $priority = filter_var($request->priority, FILTER_UNSAFE_RAW);
    }

    
    $ticket = new \App\Http\Controllers\TicketController();
    try {
        $ticketId = $ticket->store($departmentId, $user->id, $subject, $_SERVER["REMOTE_ADDR"], $priority);
    } catch (Exception $e) {
        return response()->json(["status" => "failed", "data" => "ticket creation failed"], 500);
    }

    $type = "from-client";

    $message = "";
    if (isset($request->message)) {
        $message = filter_var($request->message, FILTER_UNSAFE_RAW);
    }

    $thread = new \App\Http\Controllers\ThreadController();
    try {
        $threadId = $thread->store($ticketId, $type, $message);
    } catch (Exception $e) {
        return response()->json(["status" => "failed", "data" => "ticket thread creation failed"], 500);
    }

    if ($threadId) {
        return response()->json(["status" => "success", "ticket_id" => $ticketId], 200);
    }

});



Route::get("/ticket/{ticket}", "App\Http\Controllers\TicketController@get");
Route::get("/tickets", "App\Http\Controllers\TicketController@index");


Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

    

});


