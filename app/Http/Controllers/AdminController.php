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
            'phone' => [
                'required',
                'exists:users,phone',
                Rule::unique('users')->ignore(optional(User::where('phone', request()->input('phone'))->first())->id),
            ],
            'store_name' => 'required|unique:stores',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        Store::create([
            'user_id' => $user->id,
            'store_name' => request()->input('store_name'),
        ]);
        if ($user->status_role == 'user')
            User::where('phone', request()->input('phone'))->update(['status_role' => 'super_user']);
        return response()->json(['message' => 'Store Created Sucssfully'], 201);

    }
    public function updateStore($id)
    {
        $validator = Validator::make(request()->all(), [
            'store_name' => 'required|unique:stores',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $store = User::find($id)->first();
        if ($store) {
            Store::where('id', $id)->update([
                'store_name' => request()->input('store_name'),
            ]);
            return response()->json(['message' => 'Updated Succssfully'], 200);
        }
        return response()->json(['message' => 'Store Not Found'], 204);
    }
    public function edit($id)
    {
        $store = Store::where('id', $id)->first();
        if ($store)
            return response()->json([$store], 200);
        return response()->json(['message' => 'Store Not Found'], 400);
    }
    public function deleteStore($id)
    {
        $store = Store::where('id', $id)->first();
        $user=$store->user;
        if ($store) {
            User::find($user->id)->update(['status_role'=>'user']);
            $store->delete();
            return response()->json(['message' => 'Deleted Succssfully'], 200);
        }
        return response()->json(['message' => 'Store Not Found'], 400);
    }
    public function stores()
    {
        $stores = Store::all();
        if ($stores->isEmpty())
            return response()->json(['message' => 'No Stores Avaiable To Show'], 200);
        return response()->json($stores, 200);
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
