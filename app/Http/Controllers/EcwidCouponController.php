<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EcwidCouponController extends Controller
{
    
    public function findCoupon( $code ) {

        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            try {
                // return response([
                //     "url" => env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/discount_coupons" . "?code=" . $code
                // ]);

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
                ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/discount_coupons" . "?code=" . $code );
        
                if ( $response->successful() ) {
        
                    $couponResponse = $response->json();
        
                    if ( $couponResponse ) {
                        return response($couponResponse, 200);
                    }
        
                    return [
                        "status" => 404,
                        "message" => "No coupon found with that code."
                    ];
        
                }
            } catch (\Throwable $e) {
                return [
                    "status" => $e->getCode(),
                    "message" => $e->getMessage(),
                ];
            }

        }

    }

    public function validateCoupon( $code ) {

        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            try {
                // return response([
                //     "url" => env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/discount_coupons" . "?code=" . $code
                // ]);

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
                ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/discount_coupons" . "/" . $code );
        
                if ( $response->successful() ) {
        
                    $couponResponse = $response->json();
        
                    if ( $couponResponse ) {
                        return response($couponResponse, 200);
                    }
        
                    return [
                        "status" => 404,
                        "message" => "No coupon found with that code."
                    ];
        
                }
            } catch (\Throwable $e) {
                return [
                    "status" => $e->getCode(),
                    "message" => $e->getMessage(),
                ];
            }

        }

    }

}
