<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AcceptReceiving;
use App\Notifications\AcceptSending;
use App\Notifications\RejectReceiving;
use App\Notifications\RejectSending;
use App\Models\Order;

class AdminController extends Controller
{
    public function createStore()
    {
        $user = User::where('phone', request()->input('phone'))->first();
        $validator = Validator::make(request()->all(), [
            'phone' =>
                'required|unique:stores',
            'store_name' => 'required|unique:stores',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        if ($user) {
            $store = Store::create([
                'user_id' => $user->id,
                'phone' => request('phone'),
                'store_name' => request()->input('store_name'),
            ]);
            User::where('phone', request()->input('phone'))->update([
                'status_role' => 'super_user',
                'store_id' => $store->id
            ]);
            return response()->json(['message' => 'Store Created Sucssfully'], 201);
        }
        return response()->json(['message' => 'User Not Found'], 400);
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
        $store = Store::findOrFail($id)->first();
        if ($store) {
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
    public function searchStoreInAdmin()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $query = request()->input('name');
        $stores = Store::where('store_name', 'LIKE', "%{$query}%")->get();

        if ($stores->isEmpty())
            return response()->json(['message' => 'No Result'], 400);
        return response()->json($stores, 200);
    }
    public function acceptReceiving($id)
    {
        $order = Order::find($id);
        if (!$order || $order->status != 'sending') {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $order->update(['status' => 'receiving']);
        $user = $order->user;
        if ($user)
            $user->notify(new AcceptReceiving($id));
        return response()->json(['message' => 'Accept receiving Order Of Id' . $id]);
    }
    public function rejectReceiving($id)
    {
        $order = Order::find($id);
        if (!$order || $order->status != 'sending') {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $owner = $order->store->user;
        if ($owner->id !== auth()->id()) { // Check if the owner is the authenticated user
            return response()->json(['message' => "You can't do that."], 403);
        }

        $store = $order->store;
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }
        $user = $order->user;

        $product = $order->product;
        $amount = $product->amount;
        $amount += $order->total_amount;
        $product->update(['amount' => $amount]);
        $order->delete();
        if ($user)
            $user->notify(new RejectReceiving($id, $store->store_name));
        return response()->json(['message' => 'Reject receiving Order Of Id' . $id]);
    }
    public function acceptSending($id)
    {
        $order = Order::find($id);
        if (!$order || $order->status != 'waiting_accept') {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $order->update(['status' => 'sending']);
$carts=$order->cart;
foreach($carts as $cart){
$cart->update(['status' => 'sending']);
}
        $user = $order->user;
        if ($user) {
            $user->notify(new AcceptSending($id));

        }
        return response()->json(['message' => 'Accepted sending order of ID ' . $id]);
    }

    public function rejectSending($id)
    {

        $order = Order::find($id);

        // Check if the order exists
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Access the related store
        $store = $order->store;
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        // Access the store owner
        $owner = $store->user;
        if (!$owner || $owner->id !== auth()->id()) {
            return response()->json(['message' => 'You can\'t do that'], 403);
        }

        // Access the order's user
        $user = $order->user;
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Access the product related to the order
        $product = $order->product;
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Update product amount
        $amount = $product->amount;
        $totalAmount = $order->total_amount;

        if (!is_numeric($amount) || !is_numeric($totalAmount)) {
            return response()->json(['message' => 'Invalid amount data'], 422);
        }

        $product->update(['amount' => $amount + $totalAmount]);

        // Delete the order
        $order->delete();

        // Notify the user
        $user->notify(new RejectSending($id, $store->store_name));

        // Return response
        return response()->json(['message' => 'Rejected sending order of ID ' . $id]);
    }
    public function notifications()
    {
        $notifications = User::find(auth()->user()->id)->notifications;
        if ($notifications->isEmpty())
            return response()->json(['message' => 'No  Notifications To Show']);
        return response()->json($notifications, 200);
    }

    public function waitingOrders()
    {
        $orders = Order::where('status', 'waiting_accept')->get();
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No Items To Show'], 404);
        }

        $formattedOrders = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'user_name' => $order->user->firstName . ' ' . $order->user->lastName,
                'phone_number' => $order->user->phone,
                'total_amount' => $order->total_amount,
                'total_price' => $order->total_price,
                'address' => $order->address,
                'products'=>$order->cart,
            ];
        });

        return response()->json($formattedOrders, 200);
    }

    public function sendingOrders()
    {
        $orders = Order::where('status', 'sending')->get();
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No Items To Show'], 404);
        }

        $formattedOrders = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'user_name' => $order->user->firstName . ' ' . $order->user->lastName,
                'phone_number' => $order->user->phone,
                'total_amount' => $order->total_amount,
                'total_price' => $order->total_price,
                'address' => $order->address,
                'products'=>$order->cart,
            ];
        });

        return response()->json($formattedOrders, 200);
    }
    public function receivingOrders()
    {
        $orders = Order::where('status', 'receiving')->get();
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No Items To Show'], 404);
        }
        $formattedOrders = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'user_name' => $order->user->firstName . ' ' . $order->user->lastName,
                'phone_number' => $order->user->phone,
                'total_amount' => $order->total_amount,
                'total_price' => $order->total_price,
                'address' => $order->address,
                'products'=>$order->cart,
            ];
        });

        return response()->json($formattedOrders, 200);
    }
}
