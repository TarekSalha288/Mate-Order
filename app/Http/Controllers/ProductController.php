<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class ProductController extends Controller
{
    public function addFavorite($id)
    {
        $product = Product::find($id);
        $found=DB::table('favorite')->where('user_id',auth()->user()->id)->where('product_id',$id)->first();
       // return $found;
        if ($product) {
            if(!$found){
            DB::table('favorite')->insert([
                'user_id' => auth()->user()->id,
                'product_id' => $id,
            ]);
            return response()->json(['message' => 'Add To Favorite'], 201);
        }
        return response()->json(['message' => 'Added Before'], 400);
    }
        return response()->json(['message' => 'Product Not Found '], 400);
    }
    public function disFavorite($id)
    {
        $fav = DB::table('favorite')->where('product_id', $id)->where('user_id', auth()->user()->id)->first();
        if ($fav) {
            DB::table('favorite')->delete($fav->id);
            return response()->json(['message' => 'Deleted From Favorite'], 200);
        }
        return response()->json(['message' => 'You Can\'t Delete This '], 400);
    }
    public function showProducts($category)
    {
        $products = Product::where('category', $category)->where('active', 1)->paginate(6);
        $allProducts = [];
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No Products Available'], 400);
        }
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

    public function allProducts()
    {
        $products = Product::where('active', 1)->paginate(10);
        $allProducts = [];

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No Products Available'], 400);
        }

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
    public function searchProduct()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $query = request()->input('name');
        $products = Product::where('name', 'LIKE', "%{$query}%")->where('active', 1)->get();
        $allProducts=[];
        foreach($products as $product){
            $owner=$product->store;
            $fav = DB::table('favorite')
            ->where('user_id', auth()->user()->id)
            ->where('product_id', $product->id)
            ->exists();
            $product['owner'] = $owner->store_name;
            $product['fav'] = $fav;
            unset($product['store']);
            $allProducts[] = [
                'product' => $product,
            ];
        }
        if ($products->isEmpty())
            return response()->json(['message' => 'No Result'], 400);
        return response()->json(['data'=>$allProducts], 200);
    }


}
