<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Omnipay\Omnipay;

class PaymentController extends Controller
{
    private $gateway;

    public function __construct()
    {
        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));
        $this->gateway->setSecret(env('PAYPAL_CLIENT_SECRET'));
        $this->gateway->setTestMode(true); // set it to 'false' when go live
    }

    /**
     * Initiate a payment on PayPal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function charge(Request $request)
    {
        //Is_fast_buy must be true or false
        $is_fast_buy = $request->input('is_fast_buy');
        $totalToPay = $request->input('amount');
        $user_id = Auth::user()->user_id;
        if (!is_numeric($totalToPay) || $totalToPay <= 0) {
            return response()->json(['error' => 'Invalid total to pay'], 400);
        }
        try {
            $response = $this->gateway->purchase([
                'amount' => $totalToPay,
                'currency' => env('PAYPAL_CURRENCY'),
                'returnUrl' =>
                $is_fast_buy
                    ? route('payment.success', ['is_fast_buy' => $is_fast_buy, 'product_id' => $request->input('product_id'), 'user_id' => $user_id])
                    : route('payment.success', ['is_fast_buy' => $is_fast_buy, 'user_id' => $user_id]),
                'cancelUrl' => route('payment.error'),
            ])->send();

            if ($response->isRedirect()) {
                return response()->json(['redirect_url' => $response->getRedirectUrl()]);
            } else {
                return response()->json(['error: ' => $response->getMessage()], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Charge a payment and store the transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function success(Request $request)
    {
        // Once the transaction has been approved, we need to complete it.
        if ($request->input('paymentId') && $request->input('PayerID')) {
            $transaction = $this->gateway->completePurchase([
                'payer_id' => $request->input('PayerID'),
                'transactionReference' => $request->input('paymentId'),
            ]);
            $response = $transaction->send();
            if ($response->isSuccessful()) {
                $user_id = $request->user_id;
                // The customer has successfully paid.
                $arr_body = $response->getData();
                $is_fast_buy = $request->is_fast_buy;
                //Check if user do fast buy then insert we get only one product_id if not that mean they pay all the product in
                //cart so we get all product in cart and remove them out and add them into ordered_table
                if ($is_fast_buy) {
                    $product_id = $request->product_id;
                    $product = Product::find($product_id);
                    $product->stock_qty -= 1;
                    $product->save();
                } else {
                    $cartItems = Cart::where('user_id', $user_id)->get();
                    foreach ($cartItems as $cartItem) {
                        // Retrieve the corresponding product
                        $product = Product::find($cartItem->product_id);
                        $quantity = $cartItem->quantity;
                        if ($product) {
                            $product->stock_qty -= $quantity;
                            $product->save();
                        }
                        $cartItem->delete();
                    }
                }
                // ... (rest of the code remains the same)
                return response()->json(['success' => true, 'message' => 'Payment successful']);
            } else {
                return response()->json(['error' => $response->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => "Missing paymentId or PayerID"], 400);
        }
    }

    /**
     * Error Handling.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function error()
    {
        return response()->json(['error' => 'Payment error'], 500);
    }
}
