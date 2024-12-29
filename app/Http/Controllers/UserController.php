<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\User;
use App\UploadImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\TowFactorMail;
use App\Models\Address;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Storage;
class UserController extends Controller
{
    use UploadImageTrait;
    public function updateInfo(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $validator = Validator::make(request()->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'required|size:12|starts_with:+,963|unique:users,phone,' . Auth::id(),
            'image_path' => 'image|mimes:jpeg,png,jpg',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        if ($request->email != $user->email) {
            $user->generateCode();
            Mail::to($user->email)->send(new TowFactorMail($user->code, $user->firstName));
        }
        $user->update([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'phone' => $request->phone,

        ]);
        return response()->json(['message' => 'Info Updated Succseflly'], 200);
    }
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        if (Hash::check($request->current_password, Auth::user()->password)) {
            if ($request->password == $request->confirmation_password) {
                $password = Hash::make($request->password);
                User::where('id', auth()->user()->id)->update([
                    'password' => $password,
                ]);
                return response()->json(['message' => 'Password Changed Sucssfully']);
            }
            return response()->json(['message' => 'Password And Confirmation_Password Not The Same'], 400);
        }
        return response()->json(['message' => 'Old Password Not Correct'], 400);
    }
    public function deleteImage()
    {
        $user = auth()->user();
        $oldImagePath = $user->image_path;
        if (Storage::disk('project')->exists($oldImagePath)) {
            Storage::disk('project')->delete($oldImagePath);
            $user->update(['image_path' => 'null']);
            $user->save();
            return response()->json(['message' => 'Deleted Sucssfully'], 200);
        }
        return response()->json(['message' => 'Not Correct Path'], 400);
    }


    public function updateImage(Request $request)
    {
        $user = Auth::user();

        if ($request->hasFile('image')) {

            $oldImagePath = $user->image_path;

            if (Storage::disk('project')->exists($oldImagePath)) {
                Storage::disk('project')->delete($oldImagePath);
            }
            $path = $this->uploadImage($request, 'users', $user->id);
            $user->image_path = $path;
            $user->save();
            return response()->json(['message' => $user->image_path], 200);
        }

        return response()->json(['message' => 'No File'], 400);
    }

    public function addAddress()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        Address::create([
            'title' => request()->input('title'),
            'latitude' => request()->input('latitude'),
            'longitude' => request()->input('longitude'),
            'description' => request()->input('description'),
            'user_id' => auth()->user()->id,
        ]);
        return response()->json(['message' => 'Added Address Sucssfully'], 201);
    }
    public function showAddresses()
    {
        $addresses = User::find(auth()->user()->id)->addreses;
        if ($addresses->isEmpty())
            return response()->json(['message' => 'You Don\'t Have Addresses Yet'], 400);
        $allAdreeses = [];
        foreach ($addresses as $address) {
            $allAdreeses[] = [
                'address' => $address
            ];
        }
        return response()->json($allAdreeses, 200);
    }
    public function showPhoto()
    {
        $user = auth()->user();
        if ($user) {
            if ($user->image_path && $user->image_path !== 'null') {
                $imagePath = public_path('storage/project/' . $user->image_path);

                if (file_exists($imagePath)) {
                    return response()->file($imagePath);
                }
                return response()->json(['message' => 'Image file does not exist.'], 404);
            }
            return response()->json(['message' => 'You don\'t have a photo yet.'], 200);
        }

        return response()->json(['message' => 'User not authenticated.'], 401);
    }

    public function notifications()
    {
        $notifications = User::find(auth()->user()->id)->notifications;
        if ($notifications->isEmpty())
            return response()->json(['message' => 'No  Notifications To Show']);
        return response()->json($notifications, 200);
    }
    public function showFav()
    {
        $products = Auth::user()->products;
        if ($products->isEmpty())
            return response()->json(['message' => 'No  Favorite Products To Show']);
        return response()->json($products, 200);
    }
}
