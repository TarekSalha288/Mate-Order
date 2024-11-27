<?php

namespace App;
use Illuminate\Http\Request;
trait UploadImageTrait
{
    public function uploadImage(Request $request, $folderName,$user_name) {

        $folderName=$folderName.'/'.$user_name;
        if ($request->hasFile('image')) {
            $image = $request->file('image')->getClientOriginalName();
            $path = $request->file('image')->storeAs($folderName,$image, 'project');
         return $path;
        }

    }
}
