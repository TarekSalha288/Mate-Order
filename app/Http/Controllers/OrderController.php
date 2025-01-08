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
        return response()->json(['message'=>'Not Your Address'],400);
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
////////////////////////////////////////////////////////////////////////////////
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

public function deleteFromOrder($order_id,$product_id){
$order=auth()->user()->orders()->where('id',$order_id)->first();
if(!$order)
return response()->json(['message'=>'Order Id Not Correct'],400);

$cart=$order->cart->where('product_id',$product_id)->where('status','waiting_accept')->first();
if(!$cart)
return response()->json(['message'=>'Product Not Found'],400);
$product=$cart->product;
$product->update(['amount'=>$product->amount + $cart->total_amount]);
$order->update(['total_amount'=>$order->total_amount - 1,
'total_price'=>$order->total_price - $cart->total_price]);
auth()->user()->cart()->where('status','waiting_accept')->where('product_id',$product_id)->delete();
if($order->total_amount == 0)
    $order->delete();

return response()->json(['message'=>"Deleted $product->name From Order Id $order_id "]);
}
///////////////////////////////////////////
    public function deleteOrder($order_id)
    {
        $order=auth()->user()->orders()->where('id',$order_id)->where('status','waiting_accept')->first();
        if(!$order)
        return response()->json(['message'=>'Order Id Not Correct'],400);
        $cart=$order->cart;
        foreach($cart as $c){
        $product=$c->product;
        $product->update([
            'amount' => $product->amount + $c->total_amount
        ]);
     $product->save();
    }
      $order->delete();
      return response()->json(['message' => "Order $order_id deleted succufully"]);
    }
    public function orders()
    {
     $orders=auth()->user()->orders()->orderBy('status','desc')->get();
     if($orders->isEmpty())
     return response()->json(['message'=>'No Orders Yet'],400);
     return response()->json($orders);
    }
    public function edit($order_id){
        $orders=auth()->user()->orders()->where('id',$order_id)->first();
        if(!$orders)
        return response()->json(['message'=>'Order Not Correct'],400);
        $carts=$orders->cart;
        $all=[];
        foreach($carts as $cart){
            $all[] = [
                'productInCart' => array_merge(
                    $cart->toArray(),
                    ['store' => $cart->store->store_name,
                    'productInCart'=>$cart->product]
                ),
            ];
        }
        return response()->json($all);
    }

}
