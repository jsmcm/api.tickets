<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use \App\Models\Attachement;
use Illuminate\Mail\Mailables\Attachment;

class AttachementService
{

    public function store($uuid, $dropzoneId, $uploadedFile)
    {


        $fileName = $uploadedFile->getClientOriginalName();
            
        $path = $uploadedFile->storePubliclyAs(
            "attachements/temp/".$dropzoneId, $uuid."_".$fileName,
            "s3_file_storage"
        );
        
        
        $attachment = new Attachement();
        $attachment->random_string = $dropzoneId;
        $attachment->uuid = $uuid;
        $attachment->file_url = "https://".config("filesystems.disks.s3_file_storage.bucket").".s3.".config("filesystems.disks.s3_file_storage.region").".amazonaws.com/".$path;
        
        $attachment->save();
        
        return $path;


    }



}