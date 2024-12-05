<?php

namespace App\Http\Controllers;

use App\Models\Product;
use DB;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\IsEmpty;
use Validator;

class OrderController extends Controller
{
    public function addOrder(Request $request, $product_id, $adress_id)
    {
        // in flutter its important to put the add adress puttom to add adress id if they dont have adress id to pass it in param
        $user_id = auth()->user()->id;
        $product = Product::find($product_id);
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
        DB::table('orders')->insert([
            'total_amount' => $request->total_amount,
            'total_price' => $total_price,
            'user_id' => $user_id,
            'product_id' => $product_id,
            'address_id' => $adress_id
        ]);
        $product->update([
            'amount' => $product->amount - $request->total_amount
        ]);
        return response()->json(['message' => 'order added successfully']);
        // if we order all amount we must delete this product after accept the order in superUser
        // and if its disaccept we must return the amount to product
    }
    public function getAllOrdersInCart()
    {
        $user_id = auth()->user()->id;
        $orders = DB::table('orders')->where('send', 0)
            ->where('user_id', $user_id)
            ->get();
        // I must use scope later and tarek in this case we can use just where
        if (!$orders->isEmpty()) {
            return response()->json(['data' => $orders, 'message' => 'this is all bending order'], 200);
        }
        return response()->json(['data' => null, 'message' => 'you dont have any order'], 400);
    }
    public function updateOrder(Request $request, $order_id, $adress_id)
    {
        $order = DB::table('orders')->find($order_id);
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

                DB::table('orders')->where('id', $order_id)->update([
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
            DB::table('orders')->where('id', $order_id)->update([
                'total_amount' => $new_amount,
                'total_price' => $total_price,
                'address_id' => $adress_id
            ]);
            $product->update([
                'amount' => $product->amount + $new_total_amount
            ]);
        } else {
            DB::table('orders')->where('id', $order_id)->update([
                'total_amount' => $new_amount,
                'total_price' => $total_price,
                'address_id' => $adress_id
            ]);
        }
        return response()->json(['meassage' => 'order updated successfully']);
    }
    public function deleteOrder($order_id)
    {
        $order = DB::table('orders')->find($order_id);
        $amount = $order->total_amount;
        $product = Product::find($order->product_id);
        $deleteProduct = DB::table('orders')->where('id', $order_id)->delete();
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