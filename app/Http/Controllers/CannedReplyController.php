<?php

namespace App\Http\Controllers;

use App\Models\CannedReply;
use App\Services\CannedReplyService;
use Illuminate\Http\Request;

class CannedReplyController extends Controller
{
    //


    public function store(Request $request)
    {

        $validatedData = $request->validate([
            "use_ml"    => "required|bool",
            "message"   => "required|string",
            "title"     => "required|string",
            "department"=> "required|integer",
        ]);

        $cannedReplyService = new CannedReplyService();

        try {
            $cannedReplyService->store(
                $validatedData["message"],
                $validatedData["title"],
                $validatedData["use_ml"],
                $validatedData["department"],
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status"    => "error", 
                    "message"   => $e->getMessage()
                ], (($e->getCode() > 99 && $e->getCode() < 600)?$e->getCode():500)
            );
        }


        return response()->json(["data" => "created"], 200);

    }



    public function update(Request $request, CannedReply $cannedReply)
    {

        $validatedData = $request->validate([
            "use_ml"    => "required|bool",
            "message"   => "required|string",
            "title"     => "required|string",
        ]);

        $cannedReplyService = new CannedReplyService();

        try {
            
            $cannedReplyService->update(
                $cannedReply,
                $validatedData["use_ml"],
                $validatedData["message"],
                $validatedData["title"]
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



    public function destroy(CannedReply $cannedReply)
    {
        
        if (!auth()->user()->can("delete", $cannedReply)) {
            return response()->json([
                "Not authorized"
            ], 401);
        }

        try {
            $cannedReply->delete();
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

    public function index()
    {

        $cannedReplyService = new CannedReplyService();

        try {
            $replies = $cannedReplyService->index();
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status"    => "error", 
                    "message"   => $e->getMessage()
                ], 422
            );
        }
        

        return response()->json(["replies" => $replies], 200);
    }



    public function show(CannedReply $cannedReply)
    {

        if ( ! auth()->user()->can("view", $cannedReply)) {
            throw new \Exception("Not authorize", 401);
        }

        try {
            return response()->json(["data" => $cannedReply], 200);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status"    => "error", 
                    "message"   => $e->getMessage()
                ], 422
            );
        }
        

        return response()->json(["data" => $cannedReply], 200);
    }



}
