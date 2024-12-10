<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class StoreController extends Controller
{
public function show(){
    $stores=Store::all();
    if($stores->isEmpty())
    return response()->json(['message'=>'No Stores To Show'],200);
    return response()->json($stores,200);
}
public function edit($id){
    $store=Store::find($id);
    if($store){
    $products=$store->products()->where('active',1)->paginate(5);
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
    //$stores->toArray();
    if($stores->isEmpty())
    return response()->json(['message'=>'No Result'],400);
    return response()->json($stores,200);
}
}
