<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderSendingToSuperUser;
use DB;
use Illuminate\Http\Request;
use Validator;

class OrderController extends Controller
{
    public function addOrder(Request $request, $product_id, $adress_id)
    {
        // in flutter its important to put the add adress puttom to add adress id if they dont have adress id to pass it in param
        $user = auth()->user();
        $product = Product::find($product_id);
        $super_user = $product->store;
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $total_price = $product->price * $request->total_amount;
        if ($request->total_amount > $product->amount) {
            return response()->json(['message' => "we dont have enouth amount the total amount we have just $product->amount"]);
        }
        $order = Order::create([
            'total_amount' => $request->total_amount,
            'total_price' => $total_price,
            'user_id' => $user->id,
            'product_id' => $product_id,
            'address_id' => $adress_id,
            'store_id' => $product->store_id
        ]);
        $product->update([
            'amount' => $product->amount - $request->total_amount
        ]);
        $super_user->user->notify(new OrderSendingToSuperUser($user->firstName, $product_id, $order->id));
        return response()->json(['message' => 'order added successfully']);
        // if we order all amount we must delete this product after accept the order in superUser
        // and if its disaccept we must return the amount to product
    }
    public function getAllWaitingOrdersInCart()
    {
        $user_id = auth()->user()->id;
        $orders = Order::where('status', 'waiting')
            ->where('user_id', $user_id)
            ->get();
        // I must use scope later and tarek in this case we can use just where
        if (!$orders->isEmpty()) {
            return response()->json(['data' => $orders, 'message' => 'this is all bending order'], 200);
        }
        return response()->json(['data' => null, 'message' => 'you dont have any order'], 400);
    }// update in name in postman for not conflect
    public function getAllInWayOrder()
    {
        $user_id = auth()->user()->id;
        $orders = Order::where('status', 'sending')
            ->where('user_id', $user_id)
            ->get();
        // I must use scope later and tarek in this case we can use just where
        if (!$orders->isEmpty()) {
            return response()->json(['data' => $orders, 'message' => 'this is all in way order'], 200);
        }
        return response()->json(['data' => null, 'message' => 'you dont have any in way order'], 400);
    }
    public function getAllReceivingOrder()
    {
        $user_id = auth()->user()->id;
        $orders = Order::where('status', 'receiving')
            ->where('user_id', $user_id)
            ->get();
        // I must use scope later and tarek in this case we can use just where
        if (!$orders->isEmpty()) {
            return response()->json(['data' => $orders, 'message' => 'this is all accepted order'], 200);
        }
        return response()->json(['data' => null, 'message' => 'you dont have any accepted order'], 400);
    }
    public function updateOrder(Request $request, $order_id, $adress_id)
    {
        $order = Order::find($order_id);
        if (!$order) {
            return response()->json(['message' => 'order accepted you can,t updated the order']);
        }// if the the order accepted and stell in the cart
        $old_amount = $order->total_amount;
        $product = Product::find($order->product_id);
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $new_amount = $request->total_amount;
        $total_price = $product->price * $new_amount;
        $new_total_amount = 0;
        if ($new_amount > $old_amount) {
            $new_total_amount = $new_amount - $old_amount;
            if ($new_total_amount > $product->amount) {
                return response()->json(['message' => "we dont have enouth amount the total amount we have just $product->amount"]);
            } else {

                $order->update([
                    'total_amount' => $new_amount,
                    'total_price' => $total_price,
                    'address_id' => $adress_id
                ]);
                $product->update([
                    'amount' => $product->amount - $new_total_amount
                ]);
            }
        } elseif ($new_amount < $old_amount) {
            $new_total_amount = $old_amount - $new_amount;
            $order->update([
                'total_amount' => $new_amount,
                'total_price' => $total_price,
                'address_id' => $adress_id
            ]);
            $product->update([
                'amount' => $product->amount + $new_total_amount
            ]);
        } else {
            $order->update([
                'total_amount' => $new_amount,
                'total_price' => $total_price,
                'address_id' => $adress_id
            ]);
        }
        return response()->json(['meassage' => 'order updated successfully']);
    }
    public function deleteOrder($order_id)
    {
        $order = Order::find($order_id);
        $amount = $order->total_amount;
        $product = Product::find($order->product_id);
        $deleteProduct = $order->find($order_id)->delete();
        $product->update([
            'amount' => $product->amount + $amount
        ]);
        if ($deleteProduct) {
            return response()->json(['message' => 'order deleted succufully']);
        } else {
            return response()->json(['message' => 'order deleted failed']);
        }
    }
}
