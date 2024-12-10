<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Notifications\AcceptSending;
use App\Notifications\RejectSending;
use Illuminate\Http\Request;
use Validator;

class SuperUserController extends Controller
{
    public function createProduct(Request $request)
    {
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
        $user_id = auth()->user()->id;
        $storeOwner = Store::where('user_id', $user_id)->first();
        $store_id = $storeOwner->id;
        Product::create([
            'store_id' => $store_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'price' => $request->price,
            'category' => $request->category,
            'image_path' => $request->image_path,
        ]);
        return response()->json(['message' => 'product added successfully'], 200);}


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
    public function acceptSending($id){
        $order=Order::find($id);
        $store=$order->store;
        $owner=$order->store()->user;
        $user=$order->user;
        if($owner!=auth()->user())
        return response()->json(['message'=>'You Can\'t Do That ']);
    $order->update(['status'=>'receiving']);
     $user->notify(new AcceptSending($id,$store->store_name));
     return response()->json(['message'=>'Accept Sending Order Of Id'.$id]);
    }
    public function rejectSending($id){
        $order=Order::find($id);
        $owner=$order->store()->user;
        $store=$order->store;
        $user=$order->user;
        if($owner!=auth()->user())
        return response()->json(['message'=>'You Can\'t Do That ']);
        $order->delete();
        $user->notify(new RejectSending($id,$store->store_name));
        return response()->json(['message'=>'Reject  Sending Order Of Id'.$id]);
    }
public function waitingOrders(){
    $orders=Store::where('user_id',auth()->user()->id)->orders()->where('status','waiting');
    if($orders->isEmpty())
    return response()->json(['message'=>'No Items To Show']);
return response()->json($orders,200);

}
public function sendingOrders(){
    $orders=Store::where('user_id',auth()->user()->id)->orders()->where('status','sending');
    if($orders->isEmpty())
    return response()->json(['message'=>'No Items To Show']);
return response()->json($orders,200);
}
public function receivingOrders(){
    $orders=Store::where('user_id',auth()->user()->id)->orders()->where('status','receiving');
    if($orders->isEmpty())
    return response()->json(['message'=>'No Items To Show']);
return response()->json($orders,200);
}
public function archive(){
    $products=Store::where('user_id',auth()->user()->id)->products();
    if($products->isEmpty())
    return response()->json(['message'=>'No Items To Show']);
return response()->json($products,200);
}
public function notifications(){
    $notifications=User::find(auth()->user()->id)->notifications;
    if($notifications->isEmpty())
    return response()->json(['message'=>'No  Notifications To Show']);
return response()->json($notifications,200);
}
}
