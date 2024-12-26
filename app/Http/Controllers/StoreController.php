<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class StoreController extends Controller
{
public function show(){
    $stores=Store::paginate(10);
    if($stores->isEmpty())
    return response()->json(['message'=>'No Stores To Show'],200);
    return response()->json([
        'data' => $stores,
        'pagination' => [
            'current_page' => $stores->currentPage(),
            'last_page' => $stores->lastPage(),
            'total' => $stores->total(),
            'per_page' => $stores->perPage(),
            'next_page_url' => $stores->nextPageUrl(),
            'prev_page_url' => $stores->previousPageUrl(),
        ],
    ], 200);
}
public function edit($id)
{
    $store = Store::find($id);
    if ($store) {
        $products = Product::where('store_id', $id)->where('active', 1)->paginate(4);
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No Products For This Store To Show']);
        }
        $allProducts = [];
        foreach ($products as $product) {
            $fav = DB::table('favorite')
                ->where('user_id', auth()->user()->id)
                ->where('product_id', $product->id)
                ->exists();

            $owner = Store::where('id', $product->store_id)->first();
            $product->toArray();
            $product['owner'] = $owner->store_name;
            $product['fav'] = $fav;
            $allProducts[] = [
                'product' => $product,
            ];
        }
        return response()->json([
            'data' => $allProducts,
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
    return response()->json(['message' => 'Store Not Found'], 400);
}

public function searchStore(){
    $validator=Validator::make(request()->all(),[
        'name'=>'required',
    ]);
    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }
    $query = request()->input('name');
    $stores = Store::where('store_name', 'LIKE', "%{$query}%")->get();
    if($stores->isEmpty())
    return response()->json(['message'=>'No Result'],400);
    return response()->json(['data'=>$stores],200);
}
}
