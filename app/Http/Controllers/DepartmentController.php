<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Department;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    //

    public function index(Request $request)
    {
        // Log::write("debug", auth()->user()->level);

        $departments = null;
        
        if (auth()->user()->level < 50) {
            $departments = Department::where([
                    "user_id" => auth()->user()->id
                ])
                ->get();
        } else {

            if (isset($request->user_id)) {
                $departments = Department::where([
                    "user_id" => intVal($request->user_id)
                ])
                ->get();
            } else {
                $departments = Department::with("User")->get();
            }

        }

        if (auth()->user()->level >= 10) {
            $departments->makeVisible([
                'mail_host',
                'pop_port',
                'smtp_port',
                'mail_username',
                'email_address',
            ]);
        }

        return response()->json(
            [
                "status" => "success",
                "data" => $departments
            ]
        , 200);

    }
}
