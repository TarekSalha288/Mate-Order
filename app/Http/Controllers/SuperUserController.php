<?php

namespace App\Http\Controllers;

use App\Jobs\ActiveProductJob;
use App\Models\Order;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\UploadImageTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;
use Notification;


class SuperUserController extends Controller
{
    use UploadImageTrait;
    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required|numeric',
            'price' => 'required|numeric',
            'category' => 'required',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $storeOwner = $user->store; // Ensure store relationship is valid
        if (!$storeOwner) {
            return response()->json(['error' => 'Store not found for this user'], 404);
        }
        $store_id = $storeOwner->id;
        $product = Product::create([
            'store_id' => $store_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'price' => $request->price,
            'category' => $request->category,

        ]);

        $path = $this->uploadImage($request, 'products', $product->id);
        $product->update(['image_path' => $path]);
        $product->save();
        return response()->json(['message' => 'Product added successfully'], 200);
    }



    public function getAllProductInStore(Request $request)
    {
        $user_id = auth()->user()->id;
        $products = User::find($user_id)->store->products()->where('active', 1)->get();
        if (!$products) {
            return response()->json(['data' => null, 'message' => 'No products yet '], 400);
        }
        return response()->json(['data' => $products, 'message' => 'get products successfully'], 200);
    }
    public function updateProductInStore(Request $request, $product_id)
    {
        $user_id = auth()->user()->id;
        $products = User::find($user_id)->store->products()->where('active', 1)->get();
        if (!$products) {
            return response()->json(['data' => null, 'message' => 'products not found'], 400);
        }
        $product = $products->where('id', $product_id)->first();
        if (!$product) {
            return response()->json(['data' => null, 'message' => 'products not found'], 400);
        }
        if ($request->hasFile('image')) {

            $oldImagePath = $product->image_path;

            if (Storage::disk('project')->exists($oldImagePath)) {
                Storage::disk('project')->delete($oldImagePath);
            }
            $path = $this->uploadImage($request, 'products', $product->id);
            $product->image_path = $path;
            $product->save();
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required',
            'price' => 'required',
            'category' => 'required',
            'image_path' => 'image|mimes:jpeg,png,jpg'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $product->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'price' => $request->price,
            'category' => $request->category,
            'image_path' => $path
        ]);
        return response()->json(['message' => 'product updated successfully'], 200);
    }
    public function deleteProductInStore(Request $request, $product_id)
    {
        $user_id = auth()->user()->id;
        $products = User::find($user_id)->store->products()->where('active', 1)->get();
        if ($products->isEmpty()) {
            return response()->json(['data' => null, 'message' => 'products not found'], 400);
        }
        $product = $products->where('id', $product_id)->first();
        if (!$product) {
            return response()->json(['data' => null, 'message' => 'product not found'], 400);
        }
        $imagePath = $product->image_path;
        if (Storage::disk('project')->exists($imagePath)) {
            Storage::disk('project')->delete($imagePath);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }




    public function archive()
    {
        $products = User::find(auth()->user()->id)->store->products()->where('active', 0)->paginate(10);
        if ($products->isEmpty())
            return response()->json(['message' => 'No Items To Show']);
        return response()->json([
            'data' => $products,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
        ], 200);

    }

    public function refreshData()
    {
        $super_user_id = auth()->user()->id;
        ActiveProductJob::dispatch($super_user_id)->delay(now()->second(40));
        return response()->json(['message' => 'product updated successfully']);
    }
}
