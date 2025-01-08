<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CartController extends Controller
{
    public function create($id)
    {
 $product = Product::find($id);
$cart = User::find(auth()->user()->id)->cart()->where('product_id', $id)->first();
if (!$product)
    return response()->json(['message' => 'Product Not Found'], 400);
if ($cart)
    return response()->json(['message' => 'You Added It Before'], 400);
$storeId = is_object($product->store) ? $product->store->id : $product->store;
Cart::create([
    'user_id' => auth()->user()->id,
    'product_id' => $id,
    'store_id' => $storeId,
    'total_price' => $product->price,
]);
return response()->json(['message' => 'Added To Cart'], 201);
    }
    public function cart()
    {
        $cart = auth()->user()->cart->where('status','waiting');
        if ($cart->isEmpty()) {
            return response()->json(['message' => 'No Products Yet'], 400);
        }
$allproducts=[];
foreach ($cart as $c) {
    $allproducts[] = [
        'productInCart' => array_merge(
            $c->product->toArray(),
            ['total_amount' => $c->total_amount]
        ),
    ];
}
return response()->json(['data' => $allproducts], 200);}
    public function delete($id)
    {
        $product = Product::find($id);
        $cart = auth()->user()->cart()->where('product_id', $id)->first();
        if (!$product)
            return response()->json(['message' => 'Product Not Found'], 400);
        if (!$cart)
            return response()->json(['message' => 'You Deleted It Before'], 400);
        $cart->delete($cart->id);
        return response()->json(['message' => 'Deleted From Cart'], 200);
    }
    public function update(Request $request,$product_id){
        $validator = Validator::make(request()->all(), [
            'total_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
    $cart=auth()->user()->cart()->where('product_id',$product_id)->where('status','waiting')->first();
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
      return response()->json(['message'=>'Product Updated Sucssfully'],200);
    }
    return response()->json(['message'=>'Amount You Needed Huge'],400);

    }
}
