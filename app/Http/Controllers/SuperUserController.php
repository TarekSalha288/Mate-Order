<?php

namespace App\Http\Controllers;

use App\Jobs\ActiveProductJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Notifications\AcceptReceiving;
use App\Notifications\AcceptSending;
use App\Notifications\RejectReceiving;
use App\Notifications\RejectSending;
use Illuminate\Http\Request;
use Notification;
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
        return response()->json(['message' => 'product added successfully'], 200);
    }


    public function getAllProductInStore(Request $request)
    {
        $user_id = auth()->user()->id;
        $storeOwner = Store::where('user_id', $user_id)->first();
        $store_id = $storeOwner->id;
        $products = Product::where('store_id', $store_id)->where('active', 1)->paginate(10);
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
        $products = Product::where('store_id', $store_id)->where('active', 1)->get();
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
        $products = Product::where('store_id', $store_id)->where('active', 1)->get();
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
    public function acceptReceiving($id)
    {
        $order = Order::find($id);
        $store = $order->store;
        $owner = $order->store()->user;
        $user = $order->user;
        if ($owner != auth()->user())
            return response()->json(['message' => 'You Can\'t Do That ']);
        $order->update(['status' => 'receiving']);
        $user->notify(new AcceptReceiving($id, $store->store_name));
        return response()->json(['message' => 'Accept receiving Order Of Id' . $id]);
    }
    public function rejectReceiving($id)
    {
        $order = Order::find($id);
        $owner = $order->store()->user;
        $store = $order->store;
        $user = $order->user;
        if ($owner != auth()->user())
            return response()->json(['message' => 'You Can\'t Do That ']);
        $product = $order->product;
        $amount = $product->amount;
        $amount += $order->total_amount;
        $product->update(['amount' => $amount]);
        $order->delete();
        $user->notify(new RejectReceiving($id, $store->store_name));
        return response()->json(['message' => 'Reject receiving Order Of Id' . $id]);
    }
    public function acceptSending($id)
    {
        $order = Order::find($id);
        $store = $order->store;
        $owner = $store->user;
        $user = $order->user;
        if ($owner != auth()->user())
            return response()->json(['message' => 'You Can\'t Do That ']);
        $order->update(['status' => 'sending']);
        Notification::send($user, new AcceptSending($id, $store->store_name));
        return response()->json(['message' => 'Accept sending Order Of Id' . $id]);
    }
    public function rejectSending($id)
    {
        $order = Order::find($id);
        $store = $order->store;
        $owner = $store->user;
        $user = $order->user;
        $product = $order->product;
        $amount = $product->amount;
        if ($owner != auth()->user())
            return response()->json(['message' => 'You Can\'t Do That ']);
        $amount += $order->total_amount;
        $product->update(['amount' => $amount]);
        $order->delete();
        Notification::send($user, new RejectSending($id, $store->store_name));
        return response()->json(['message' => 'reject sending Order Of Id' . $id]);
    }
    public function waitingOrders()
    {
        $orders = User::find(auth()->user()->id)->store->orders()->where('status', 'waiting')->get();
        if (!$orders)
            return response()->json(['message' => 'No Items To Show']);
        $formattedOrders = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'user_name' => $order->user->firstName . ' ' . $order->user->lastName,
                'phone_number' => $order->user->phone,
                'total_amount' => $order->total_amount,
                'total_price' => $order->total_price,
                'product_id' => $order->product_id,
                'product_name' => $order->product->name,
                'width' => $order->address->width,
                'tall' => $order->address->tall,
                'note' => $order->address->note,
            ];
        });
        return response()->json($formattedOrders, 200);
    }
    public function sendingOrders()
    {
        $orders = User::find(auth()->user()->id)->store->orders()->where('status', 'sending')->get();
        if ($orders)
            return response()->json(['message' => 'No Items To Show']);
        $formattedOrders = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'user_name' => $order->user->firstName . ' ' . $order->user->lastName,
                'phone_number' => $order->user->phone,
                'total_amount' => $order->total_amount,
                'total_price' => $order->total_price,
                'product_id' => $order->product_id,
                'product_name' => $order->product->name,
                'width' => $order->address->width,
                'tall' => $order->address->tall,
                'note' => $order->address->note,
            ];
        });
        return response()->json($formattedOrders, 200);
    }
    public function receivingOrders()
    {
        $orders = User::find(auth()->user()->id)->store->orders()->where('status', 'receiving')->get();
        if ($orders)
            return response()->json(['message' => 'No Items To Show']);
        $formattedOrders = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'user_name' => $order->user->firstName . ' ' . $order->user->lastName,
                'phone_number' => $order->user->phone,
                'total_amount' => $order->total_amount,
                'total_price' => $order->total_price,
                'product_id' => $order->product_id,
                'product_name' => $order->product->name,
                'width' => $order->address->width,
                'tall' => $order->address->tall,
                'note' => $order->address->note,
            ];
        });
        return response()->json($formattedOrders, 200);
    }
    public function archive()
    {
        $products = Store::where('user_id', auth()->user()->id)->products()->where('active', 0)->paginate(10);
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
    public function notifications()
    {
        $notifications = User::find(auth()->user()->id)->notifications;
        if ($notifications->isEmpty())
            return response()->json(['message' => 'No  Notifications To Show']);
        return response()->json($notifications, 200);
    }
    public function refreshData()
    {
        $super_user_id = auth()->user()->id;
        ActiveProductJob::dispatch($super_user_id)->delay(now()->second(40));
        return response()->json(['message' => 'product updated successfully']);
    }
}
