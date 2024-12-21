<?php
namespace App\Http\Controllers;

use App\Mail\TowFactorMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|size:13|starts_with:+,963|unique:users,phone',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Check for expired unverified user
        $existingUser = User::where('email', request()->email)->first();
        if ($existingUser && optional($existingUser->expire_at)->isPast()) {
            $existingUser->delete();
        }

        $user = new User;
        $user->firstName = request()->firstName;
        $user->lastName = request()->lastName;
        $user->phone = request()->phone;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        $user->generateCode();

        $credentials = request(['phone', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Mail::to($user->email)->send(new TowFactorMail($user->code, $user->firstName));

        $user->fcm_token = $token;
        $user->save();

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login()
    {
        $credentials = request(['phone', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user=auth()->user();
        $user->fcm_token=$token;
        $user->save();
        return $this->respondWithToken($token);
    }
    public function me()
    {
        return response()->json(auth()->user());
    }
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 43200
        ]);
    }
    public function verify(){
        $validator = Validator::make(request()->all(), [
            'code' => 'required',
        ]);
         if ($validator->fails()) {
             return response()->json($validator->errors()->toJson(), 400);
 }
        if(auth()->check()){
        $user=auth()->user();
        if($user->expire_at < now()){
            User::destroy($user->id);
        return response()->json(['message'=>'You Should SignUp Again'],401);
        }
       if(request()->input('code')== $user->code){
         $user->code=null;
         $user->expire_at=null;
         $user->save();
        return response()->json(['message'=>'Code Is Correct'],200);
       }
       return response()->json(['message'=>'Code Is Not Correct '],400);
    }
    return response()->json(['message'=>'You Should Signup Before '],400);
    }
    public function resendCode(){
    $user=auth()->user();
    if(!auth()->check())
    return response()->json(['message'=>'Unauthorized'],401);
    if($user->expire_at < now()){
        User::destroy($user->id);
    return response()->json(['message'=>'You Should SignUp Again'],401);
    }
$user->code=rand(100000,999999);
$user->save();
Mail::to($user->email)->send(new TowFactorMail($user->code,$user->firstName));
return response()->json(['message'=>'Code Is Sending']);
    }
   }


