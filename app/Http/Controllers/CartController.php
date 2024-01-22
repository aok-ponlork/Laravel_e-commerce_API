<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Auth::user()->user_id;
        $carts = Cart::where('user_id', $user_id)->get();
        if ($carts->isEmpty()) {
            return $this->error(
                '',
                'Opp something went wrong',
                404
            );
        }
        return $this->success(
            $carts,
            'Success',
            200
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::user()->user_id;
        $product_id = $request->input('product_id');
        $quantity = $request->input('quantity');
        // Fetch the product price based on product_id
        $product = Product::where('product_id', $product_id)->first();
        $rules = [
            'product_id' => [
                'required',
                Rule::exists('products', 'product_id'),
            ],
            'quantity' => 'required|integer|min:1',
        ];

        // Validate the request
        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error(
                $validator->errors(),
                'Validation Error',
                422
            );
        }
        $cart = Cart::create([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'product_price' => $product->price
        ]);

        return $this->success(
            [
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity
            ],
            'Success',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = Cart::find($id);
        if (!$cart) {
            return $this->error(
                '',
                'Not found',
                404
            );
        }
        $cart->delete();
        return response()->noContent();
    }

    public function decrementOrIncrement(String $action, Request $request)
    {
        $cart_id = $request->cart_id;
        // Retrieve the cart by its ID
        $cart = Cart::find($cart_id);
        // Check if the cart exists
        if (!$cart) {
            return $this->error('', 'Not found', 404);
        }
        // Perform the decrement or increment operation
        if ($action == 'decrement') {
            $cart->quantity -= 1;
        } elseif ($action == 'increment') {
            $cart->quantity += 1;
        } else {
            return $this->error('', 'Invalid action', 400);
        }
        // Save the updated quantity to the database
        $cart->save();
        // Return a response indicating success
        return $this->success('Operation successful', 200);
    }


    public function removeAll()
    {
        $user_id = Auth::user()->user_id;
        $deletedRows = Cart::where('user_id', $user_id)->delete();
        if ($deletedRows === 0) {
            return $this->error(
                '',
                'Opp something went wrong',
                404
            );
        }
        return response()->noContent();
    }
}
