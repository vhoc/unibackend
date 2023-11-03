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
            Stripe::setApiKey(config( $sk ));

            // Use an existing Customer ID if this is a returning customer.
            $customer = Customer::create();
            $ephemeralKey = EphemeralKey::create(
                ['customer' => $customer->id],
                ['api_version' => '2022-08-01']
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
