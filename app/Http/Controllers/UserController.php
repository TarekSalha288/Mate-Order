<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\UploadImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\TowFactorMail;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use UploadImageTrait;
    public function updateInfo(Request $request)
    {

        $user = User::find(auth()->user()->id);

        $validator = Validator::make(request()->all(), [
            'firstName' => 'required',
            'lastName'=>'required',
            'email'=>'required|email|unique:users,email,' . Auth::id(),
            'phone'=>'required|size:12|starts_with:+,963|unique:users,phone,' . Auth::id(),
            'image_path' => 'image|mimes:jpeg,png,jpg',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
      if($request->email !=$user->email){
        $user->generateCode();
        Mail::to($user->email)->send(new TowFactorMail($user->code));
      }
        $user->update([
            'firstName' => $request->firstName,
            'lastName'=>$request->lastName,
            'email'=>$request->email,
            'phone' => $request->phone,

        ]);
        return response()->json(['message' => 'Info Updated Succseflly'], 200);

    }

    public function updatePassword(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'current_password'=>'required',
'password'=>'required|min:8',
'confirmation_password'=>'required|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        if(Hash::check($request->current_password,Auth::user()->password)){
    if($request->password==$request->confirmation_password){
        User::where('id',auth()->user()->id)->update([
            'password'=>$request->password,
        ]);
        return response()->json(true);
    }
}
    return response()->json(false);
    }
public function deleteImage(){
    $user=auth()->user();
    if (request()->hasFile('image')){
        $destenation='public/imgs/'.$user->id.'/'.$user->image_path;
        if (file_exists($destenation)){
       File::delete($destenation);
       }
}}
public function updateImage(){
    $user=User::find(auth()->user()->id)->first();
    if (request()->hasFile('image')){

        $destenation='public/imgs/'.$user->image_path;
        if (file_exists($destenation)){
       File::delete($destenation);
       }

       $path=$this->uploadImage(request(),'users',$user->id);
       $user->image_path = $path;
     }
 $user->save();
 return response()->json('Image Updated Succssful');
}


}
