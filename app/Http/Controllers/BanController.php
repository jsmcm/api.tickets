<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use Illuminate\Http\Request;

class BanController extends Controller
{
    //

    public function index()
    {
        return response()->json(Ban::get(), 200);
    }



    public function destroy(Ban $ban)
    {
        
        if (!auth()->user()->can("delete", $ban)) {
            return response()->json([
                "Not authorized"
            ], 401);
        }

        try {
            $ban->delete();
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


}
