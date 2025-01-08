<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderSendingToSuperUser;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function addOrder( $address_id)
    {
        $address=auth()->user()->addreses->where('id',$address_id);

        if($address->isEmpty())
        return response()->json(['message'=>'Not Your Address']);
        $total_price = 0;
        $user = auth()->user();
        $cart = $user->cart()->where('status', 'waiting')->get();
        if ($cart->isEmpty()) {
            return response()->json(['message' => 'You don\'t have anything in the cart to add to an order'], 400);
        }
        $admin = User::where('status_role', 'admin')->first();
        foreach ($cart as $c) {
            $product=$c->product;
            if ($c->total_amount > $product->amount) {
                return response()->json([
                    'message' => "We don't have enough of {$product->name}. The total available amount is just {$product->amount}."
                ], 400);
            }
            $product->update([
                'amount' => $product->amount - $c->total_amount,
            ]);
            $total_price += $c->total_price;
        }
        $order = Order::create([
            'total_amount' => $cart->count(),
            'total_price' => $total_price,
            'user_id' => $user->id,
            'status' => 'waiting_accept',
            'address_id' => $address_id,
        ]);
            $admin->notify(new OrderSendingToSuperUser($user->firstName, $c->product_id, $order->id));
        foreach($cart as $c){

            $c->update([
                'order_id' => $order->id
            ]);
            $c->update([
                'status' => 'waiting_accept'
            ]);
    }
    $admin->notify(new OrderSendingToSuperUser($user->firstName,  $order->id));
    return response()->json(['message' => 'order added successfully']);

}

    public function getAllWaitingOrdersInCart()
    {
        $user_id = auth()->user()->id;
        $orders = User::find($user_id)->orders()->where('status', 'waiting')->get();
        // I must use scope later and tarek in this case we can use just where
        if (!$orders->isEmpty()) {
            return response()->json(['data' => $orders, 'message' => 'this is all bending order'], 200);
        }
        return response()->json(['data' => null, 'message' => 'you dont have any order'], 400);
    }// update in name in postman for not conflect
    public function getAllInWayOrder()
    {
        $user_id = auth()->user()->id;
        $orders = User::find($user_id)->orders()->where('status', 'sending')->get();
        // I must use scope later and tarek in this case we can use just where
        if (!$orders->isEmpty()) {
            return response()->json(['data' => $orders, 'message' => 'this is all in way order'], 200);
        }
        return response()->json(['data' => null, 'message' => 'you dont have any in way order'], 400);
    }
    public function getAllReceivingOrder()
    {
        $user_id = auth()->user()->id;
        $orders = User::find($user_id)->orders()->where('status', 'receiving')->get();
        // I must use scope later and tarek in this case we can use just where
        if (!$orders->isEmpty()) {
            return response()->json(['data' => $orders, 'message' => 'this is all accepted order'], 200);
        }
        return response()->json(['data' => null, 'message' => 'you dont have any accepted order'], 400);
    }
    public function updateOrder(Request $request, $order_id, $product_id)
    {
        $validator = Validator::make(request()->all(), [
            'total_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $order=auth()->user()->orders()->where('status','waiting_accept')->where('id',$order_id)->first();
        if(!$order)
        return response()->json(['message'=>'Order Not Found'],400);
    $cart=$order->cart()->where('status','waiting_accept')->where('product_id',$product_id)->first();

    if(!$cart)
    return response()->json(['message'=>'Product Not Found'],400);
    $product=$cart->product;
    $price=$product->price;
    $total_price=$price * $request->total_amount;
        if(($request->total_amount) <= $product->amount ){
      $cart->update([
        'total_amount'=>$request->total_amount,
        'total_price'=>$total_price,
      ]);

    $product->update([
        'amount' => $product->amount - $request->total_amount ,
    ]);
    $newprice=0;
$carts=$order->cart;
    foreach($carts as $cart ){
       $newprice+= $cart->total_price;
    }
    $order->update(['total_price'=>$newprice]);
      return response()->json(['message'=>'Product Updated Sucssfully'],200);
    }
    return response()->json(['message'=>'Amount You Needed Huge'],400);
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
