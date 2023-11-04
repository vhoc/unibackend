<?php

namespace App\Http\Controllers;

// require_once('vendor/autoload.php');
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\EphemeralKey;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    
    public function paymentSheet( Request $request ) {

        $sk = (string)env('STRIPE_SECRET_KEY');
        
        try {
            Stripe::setApiKey( 'sk_test_51O8RA6LuyaSDkpjFLFQD6JlvqhqeOG7gxPE54WQrbqHPpM2gUVvsn5Gr9jkUluc1dYaCqjzIOiAOhYKbMJrSALLg00UAKUmd8w' );

            // Use an existing Customer ID if this is a returning customer.
            $customer = Customer::create();
            $ephemeralKey = EphemeralKey::create(
                ['customer' => $customer->id],
                ['stripe_version' => '2022-08-01']
            );

            $paymentIntent = PaymentIntent::create([
                'amount' => $request->input('amount'),
                'currency' => 'mxn',
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'paymentIntent' => $paymentIntent->client_secret,
                'ephemeralKey' => $ephemeralKey->secret,
                'customer' => $customer->id,
                'publishableKey' => config('services.stripe.key'),
            ]);

        } catch (\Throwable $e) {
            // return response()->json($error, $error->getHttpStatus());
            return response([
                "status" => $e->getCode(),
                "message" => $e->getMessage(),
            ]);
        }

    }

    public function key() {
        if ( env("STRIPE_PUBLISHABLE_KEY") ) {
            return response([
                "pk" => env("STRIPE_PUBLISHABLE_KEY"),
            ], 200);
        }
    }

}
