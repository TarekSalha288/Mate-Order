<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function create($id){
$product=Product::find($id);
$cart=User::find(auth()->user()->id)->cart()->where('product_id',$id)->first();
if(!$product)
return response()->json(['message'=>'Product Not Found'],400);
if($cart)
return response()->json(['message'=>'You Added It Before'],400);
Cart::create([
    'user_id'=>auth()->user()->id,
    'product_id'=>$id
]);
return response()->json(['message'=>'Added To Cart'],201);
    }
    public function cart()
{

    $cart = auth()->user()->cart;
    if ($cart->isEmpty()) {
        return response()->json(['message' => 'No Products Yet'], 400);
    }
    $cartWithoutPivot = $cart->map(function ($product) {
        $productArray = $product->toArray();
        unset($productArray['pivot']);
        return $productArray;
    });

    return response()->json(['data' => $cartWithoutPivot], 200);
}

    public function delete($id){
        $product=Product::find($id);
        $cart=User::find(auth()->user()->id)->cart()->where('product_id',$id)->first();
        if(!$product)
        return response()->json(['message'=>'Product Not Found'],400);
        if(!$cart)
        return response()->json(['message'=>'You Deleted It Before'],400);
auth()->user()->cart()->detach($id);
return response()->json(['message'=>'Deleted From Cart'],200);
    }
}
