<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TicketController;
use App\Models\Ticket;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Services\TicketService;

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






Route::group([

    'middleware' => 'api',

], function ($router) {


    Route::get("/tickets/search", "App\Http\Controllers\TicketController@search");
    Route::get("/tickets/user-search", "App\Http\Controllers\TicketController@userSearch");

    Route::get("/tickets", "App\Http\Controllers\TicketController@index");
    Route::post("/tickets", "App\Http\Controllers\TicketController@store");
    Route::patch("/tickets/{ticket}", "App\Http\Controllers\TicketController@update");
    Route::get("/tickets/{ticket}", "App\Http\Controllers\TicketController@show");
    Route::delete("/tickets/{ticket}", "App\Http\Controllers\TicketController@destroy");

    Route::post("/attachment", "App\Http\Controllers\AttachementController@store");

    Route::post("/thread/{ticket}", "App\Http\Controllers\ThreadController@store");
    Route::get("/thread/canned-replies", "App\Http\Controllers\ThreadController@index");
    Route::get("/thread/{thread}", "App\Http\Controllers\ThreadController@show");




    Route::get("/canned-replies", "App\Http\Controllers\CannedReplyController@index");
    Route::post("/canned-replies", "App\Http\Controllers\CannedReplyController@store");
    Route::delete("/canned-replies/{cannedReply}", "App\Http\Controllers\CannedReplyController@destroy");
    Route::patch("/canned-replies/{cannedReply}", "App\Http\Controllers\CannedReplyController@update");
    Route::get("/canned-replies/{cannedReply}", "App\Http\Controllers\CannedReplyController@show");


    /**
     * Merge a ticket
     */
    Route::patch("/tickets/merge/{ticket}", function(Ticket $ticket, Request $request) {
    
        
        // 
        Log::write("debug", "merge: ".$ticket->id." with ".$request->ticket_id);
        $mergeWith = Ticket::find($request->ticket_id);
    
        if ($mergeWith == null) {
            return response()->json(["status" => "failed", "data" => "Merge ticket does not exist"], 500);
        }
    
        try {
    
            $ticketService = new TicketService();
            
            if ($ticketService->merge($mergeWith, $ticket)) {
                return response()->json(["status" => "success", "data" => "ticket merged"], 200);
            }
    
        } catch (Exception $e) {
            return response()->json(["status" => "failed", "data" => $e->getMessage()], 401);
        }
    
        
    });
    

    Route::get("/departments", "App\Http\Controllers\DepartmentController@index");
    Route::get("/departments/{department}", "App\Http\Controllers\DepartmentController@show");
    Route::delete("/departments/{department}", "App\Http\Controllers\DepartmentController@destroy");
    Route::patch("/departments/{department}", "App\Http\Controllers\DepartmentController@update");

});

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'App\Http\Controllers\AuthController@login');
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
    Route::post('me', 'App\Http\Controllers\AuthController@me');

});


Route::get("create-ticket", "App\Http\Controllers\TicketController@createTicket");
