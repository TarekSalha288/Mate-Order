<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
class UserController extends Controller
{
    public function updateFirstName(Request $request, $id)
    {
        $validator = Validator::make(request()->all(), [
            'firstName' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 200);
        }
        $user->update([
            'firstName' => $request->firstName
        ]);
        return response()->json(['message' => 'first Name updated succseflly'], 200);

    }
    public function updateLastName(Request $request, $id)
    {
        $validator = Validator::make(request()->all(), [
            'lastName' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 200);
        }
        $user->update([
            'lastName' => $request->lastName
        ]);
        return response()->json(['message' => 'last Name updated succseflly'], 200);
    }
    public function updatePhone(Request $request, $id)
    {
        $validator = Validator::make(request()->all(), [
            'phone' => 'size:13|starts_with:+,963|unique:users',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 200);
        }
        $user->update([
            'phone' => $request->phone
        ]);
        return response()->json(['message' => 'phone updated succseflly'], 200);
    }
    public function updateImage(Request $request, $id)
    {
        $validator = Validator::make(request()->all(), [
            'image_path' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 200);
        }
        $user->updateOrCreate([
            'image_path' => $request->image_path
        ]);
        return response()->json(['message' => 'Image updated succseflly'], 200);
    }// here we will complete it when we create 
    public function updatePassword(Request $request, $id)
    {
        $validator = Validator::make(request()->all(), [
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 200);
        }
        $user->update([
            'password' => $request->password
        ]);
        return response()->json(['message' => 'password updated succseflly'], 200);
    }

    // in update first and last name and image I put varefied because when I update my profile I may delete my name and click update potom

}
