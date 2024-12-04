<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

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
    $products=$store->products()->paginate(5);
    if($products->isEmpty())
    return response()->json(['message'=>'No Products For This Store To Show'],400);
    return response()->json($products,200);
}
return response()->json(['message'=>'Store Not Found'],400);
}

}
