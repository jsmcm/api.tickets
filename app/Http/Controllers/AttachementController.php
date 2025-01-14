<?php
declare(strict_types=1);


namespace App\Http\Controllers;

use \App\Services\AttachementService;

use Illuminate\Http\Request;


class AttachementController extends Controller
{
    //

    function store(Request $request)
    {

        $uuid = "";
        if (isset($request->uuid)) {
            $uuid = filter_var($request->uuid, FILTER_UNSAFE_RAW);
        } else {
            throw new \Exception("Missing uuid");
        }

        $dropzoneId = "";
        if (isset($request->dropzoneId)) {
            $dropzoneId = filter_var($request->dropzoneId, FILTER_UNSAFE_RAW);
        } else {
            throw new \Exception("Missing dropzoneId");
        }

        $uploadedFile = null;
        if ($request->hasFile("file")) {
            
            $uploadedFile = $request->file("file");
    
        } else {
            throw new \Exception("No file uploaded");
        }





        $attachmentService = new AttachementService();

        $path = false;

        try {
            $path = $attachmentService->store($uuid, $dropzoneId, $uploadedFile);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "status"    => "error", 
                    "message"   => $e->getMessage()
                ], 422
            );
        }
        

        if ($path !== false) {

            return response()->json(
                [
                    "status"    => "success", 
                    "path"      => $path
                ], 200
            );

        }



    }
}
