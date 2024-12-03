<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
class AdminController extends Controller
{
    public function createStore()
    {
        $user = User::where('phone', request()->input('phone'))->first();
        $validator = Validator::make(request()->all(), [
            'phone' => ['required', Rule::unique('users')->ignore($user->id)],
            'store_name' => 'required|unique:stores',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        if (!$user) {
            return response()->json(['message' => 'Invalid Phone'], 400);
        }
        if ($user && $user->expire_at == null && $user->code == null) {
            Store::create([
                'user_id' => $user->id,
                'store_name' => request()->input('store_name'),
            ]);
            User::where('phone', request()->input('phone'))->update(['status_role' => 'super_user']);
            return response()->json(['message' => 'Store Created Sucssfully'], 201);
        }
    }
    public function deleteAccount()
    {

        $user = User::where('phone', request()->input('phone'))->first();
        if (!$user)
            return response()->json(['message' => 'User Not Found '], 400);
        if (auth()->user()->id == $user->id)
            return response()->json(['message' => 'You Can\'t Do That ']);
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'Deleted Sucssfully'], 200);
        }

    }
}
