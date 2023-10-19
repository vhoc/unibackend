<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EcwidProductCombinationController extends Controller
{
    public function getCombinations( $productId ) {

        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/products/" . $productId . "/combinations" );
    
            if ( $response->successful() ) {
    
                $combinationsResponse = $response->json();
    
                if ( $combinationsResponse ) {
                    // return $combinationsResponse;
                    return response($combinationsResponse, 200);
                }
    
                return [
                    "status" => 404,
                    "message" => "No combinations were found for this product on Ecwid."
                ];
    
            }

            return response([
                "status" => 500,
                "message" => $validEcwidConfig["message"]
            ], 500);

        }

        return [
            "status" => 400,
            "message" => $validEcwidConfig["message"]
        ];

    }
}
