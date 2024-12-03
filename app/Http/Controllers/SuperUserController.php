<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Validator;

class SuperUserController extends Controller
{
    public function createProduct(Request $request)
    {
        $user_id = auth()->user()->id;
        $storeOwner = Store::where('user_id', $user_id)->first();
        $store_id = $storeOwner->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required',
            'price' => 'required',
            'category' => 'required',
            'image_path' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        Product::create([
            'store_id' => $store_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'price' => $request->price,
            'category' => $request->category,
            'image_path' => $request->image_path
        ]);
        return response()->json(['message' => 'product added successfully'], 200);
    }
    public function getAllProductInStore(Request $request)
    {
        $user_id = auth()->user()->id;
        $storeOwner = Store::where('user_id', $user_id)->first();
        $store_id = $storeOwner->id;
        $products = Product::where('store_id', $store_id)->paginate(10);
        if (!$products) {
            return response()->json(['data' => null, 'message' => 'get products failed'], 400);
        }
        return response()->json(['data' => $products, 'message' => 'get products successfully'], 200);
    }
    public function updateProductInStore(Request $request, $product_id)
    {
        $user_id = auth()->user()->id;
        $storeOwner = Store::where('user_id', $user_id)->first();
        if (!$storeOwner) {
            return response()->json(['message' => 'Store not found'], 404);
        }
        $store_id = $storeOwner->id;
        $products = Product::where('store_id', $store_id)->get();
        if (!$products) {
            return response()->json(['data' => null, 'message' => 'products not found'], 400);
        }
        $product = $products->where('id', $product_id)->first();
        if (!$product) {
            return response()->json(['data' => null, 'message' => 'products not found'], 400);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required',
            'price' => 'required',
            'category' => 'required',
            'image_path' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $product->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'price' => $request->price,
            'category' => $request->category,
            'image_path' => $request->image_path
        ]);
        return response()->json(['message' => 'product updated successfully'], 200);
    }
    public function deleteProductInStore(Request $request, $product_id)
    {
        $user_id = auth()->user()->id;
        $storeOwner = Store::where('user_id', $user_id)->first();
        if (!$storeOwner) {
            return response()->json(['message' => 'Store not found'], 404);
        }
        $store_id = $storeOwner->id;
        $products = Product::where('store_id', $store_id)->get();
        if (!$products) {
            return response()->json(['data' => null, 'message' => 'products not found'], 400);
        }
        $product = $products->where('id', $product_id)->first();
        if (!$product) {
            return response()->json(['data' => null, 'message' => 'products not found'], 400);
        }
        $product->delete();
        return response()->json(['message' => 'product deleted succssefully'], 200);
    }
}
