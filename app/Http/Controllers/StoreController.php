<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
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
public function edit($id){
    $store=Store::find($id);
    if($store){
    $products=$store->products()->where('active',1)->paginate(6);
    if($products->isEmpty())
    return response()->json(['message'=>'No Products For This Store To Show'],400);
    return response()->json($products,200);
}
return response()->json(['message'=>'Store Not Found'],400);
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
