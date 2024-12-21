<?php

namespace App\Http\Controllers;

use App\Jobs\ActiveProductJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\UploadImageTrait;
use App\Notifications\AcceptReceiving;
use App\Notifications\AcceptSending;
use App\Notifications\RejectReceiving;
use App\Notifications\RejectSending;
use File;
use Illuminate\Http\Request;
use Notification;
use Validator;

class SuperUserController extends Controller
{
    use UploadImageTrait;
    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required',
            'price' => 'required',
            'category' => 'required',
            'image_path' => 'image|mimes:jpeg,png,jpg',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = auth()->user();
        $storeOwner = User::find($user->id)->store->first();
        $store_id = $storeOwner->id;
        $path = $this->uploadImage($request, 'products', $store_id);
        //return response()->json($path);
        Product::create([
            'store_id' => $store_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'price' => $request->price,
            'category' => $request->category,
            'image_path' => $path,
        ]);
        return response()->json(['message' => 'product added successfully'], 200);
    }


    public function getAllProductInStore(Request $request)
    {
        $user_id = auth()->user()->id;
        $products = User::find($user_id)->store->products()->where('active', 1)->paginate(10);
        if (!$products) {
            return response()->json(['data' => null, 'message' => 'get products failed'], 400);
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
            $destination = public_path('imgs/products/' . $product->store_id . '/' . $product->image_path);
            if (File::exists($destination)) {
                File::delete($destination);
            }
            $path = $this->uploadImage($request, 'products', $product->store_id);
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
        $order = Order::find($id); // Fetch the order
        if (!$order || $order->status != 'waiting') {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $store = $order->store; // Access the related store
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $owner = $store->user; // Access the user (owner of the store)
        if ($owner->id !== auth()->id()) { // Check if the owner is the authenticated user
            return response()->json(['message' => "You can't do that."], 403);
        }

        // Update order status
        $order->update(['status' => 'sending']);

        // Notify the order's user
        $user = $order->user; // Access the related user
        if ($user) {
            Notification::send($user, new AcceptSending($id, $store->store_name));
        }

        return response()->json(['message' => 'Accepted sending order of ID ' . $id]);
    }

    public function rejectSending($id)
    {
        // Fetch the order
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
