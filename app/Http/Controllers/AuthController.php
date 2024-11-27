<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Mail\TowFactorMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{


    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'phone' => 'required|size:12|starts_with:+,963|unique:users',
            'email'=>'required|email|unique:users',
            'password' => 'required|confirmed|min:8',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }


        $user = new User;
        $user->firstName = request()->firstName;
        $user->lastName = request()->lastName;
        $user->phone = request()->phone;
        $user->password = bcrypt(request()->password);
        $user->email=request()->email;
        $user->save();

        $credentials = request(['phone', 'password']);
        $token = auth()->attempt($credentials);
        $user->generateCode();
        Mail::to($user->email)->send(new TowFactorMail($user->code,$user->firstName));
        return response()->json(['user'=>$user,'token'=>$token], 201);
    }
    public function login()
    {
        $credentials = request(['phone', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 43200 // refresh in one month
        ]);
    }
    public function verify(){
        $user=auth()->user();
       if(request()->input('code')== $user->code){
         $user->code=null;
         $user->expire_at=null;
         $user->save();
        return response()->json(true);
       }
       return response()->json(false);
    }

   }


