<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function addFavorite($id){
    $product=Product::find($id);
    if($product){
DB::table('favorite')->insert([
'user_id'=>auth()->user()->id,
'product_id'=>$id,
]);
return response()->json(['message'=>'Add To Favorite'],201);}
return response()->json(['message'=>'Product Not Found '],400);
    }
    public function disFavorite($id){
$fav=DB::table('favorite')->where('product_id',$id)->where('user_id',auth()->user()->id)->first();
if($fav){
    DB::table('favorite')->delete($fav->id);
    return response()->json(['message'=>'Deleted From Favorite'],200);
}
return response()->json(['message'=>'You Can\'t Delete This '],400);
    }

}
