<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use PhpParser\Node\Expr\Cast\String_;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    use HttpResponses;
    public function getAllProducts()
    {
        $products = Product::all();
        return ProductResource::collection($products);
    }
    public function getProductByCategory(String $category_id)
    {
        $category = Category::find($category_id);
        if(!$category){
            return $this->error('', 'Not found Category with the giving category', 404);
        }
        $products = $category->products;
        // Return the products using the ProductResource
        return ProductResource::collection($products);
    }

    public function getProductByID(String $id){
        $product = Product::find($id);
        if(!$product){
            return $this->error('','Opp not found Product with ' . $id , 404);
        }
        return new ProductResource($product);
    }

    public function getProductImage($filename)
    {
        $path = public_path('images/products/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $file = file_get_contents($path);

        return Response::make($file, 200, [
            'Content-Type' => 'image/jpeg',
        ]);
    }
}
