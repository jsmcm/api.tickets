<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\DepartmentService;
use App\Models\Department;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    //



    public function update(Request $request, Department $department)
    {


        $validatedData = $request->validate([
            "departmentEmail"   => "required|email",
            "department"        => "required|string",
            "signature"         => "required|string",
            "logoUrl"           => "required|string",
            "mailHost"          => "required|string",
            "mailUsername"      => "required|string",
            "mailPassword"      => "required|string",
            "popPort"           => "required|integer",
            "smtpPort"          => "required|integer",
            "apiBaseUrl"        => "string",
            "apiToken"          => "string"
        ]);

        $departmentService = new DepartmentService();

        try {
            
            $departmentService->update(
                $department,
                $validatedData["departmentEmail"],
                $validatedData["department"],
                $validatedData["signature"],
                $validatedData["logoUrl"],
                $validatedData["mailHost"],
                $validatedData["mailUsername"],
                $validatedData["mailPassword"],
                $validatedData["popPort"],
                $validatedData["smtpPort"],
                $validatedData["apiBaseUrl"],
                $validatedData["apiToken"]
            );

        } catch (\Exception $e) {
            return response()->json(
                [
                    "status"    => "error", 
                    "message"   => $e->getMessage()
                ], (($e->getCode() > 99 && $e->getCode() < 600)?$e->getCode():500)
            );
        }

        return response()->json(["data" => "updated"], 200);
    
    }





    public function destroy(Department $department)
    {
        
        if (!auth()->user()->can("delete", $department)) {
            return response()->json([
                "Not authorized"
            ], 401);
        }

        try {
            $department->delete();
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status"    => "error", 
                    "message"   => $e->getMessage()
                ], 422
            );
        }

        return response()->json(["data" => "deleted"], 200);
    
    }


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
    

    public function show(Department $department)
    {
        // Log::write("debug", auth()->user()->level);

 
        if (auth()->user()->level < 50) {
            
            return response()->json(["Not Authorized", 500]);

        } 

        
        $department->makeVisible([
            'mail_host',
            'pop_port',
            'smtp_port',
            'mail_username',
            'mail_password',
            'email_address',
        ]);
    
        
        return response()->json(
            [
                "status"        => "success",
                "department"    => $department
            ]
        , 200);

    }
    

}
