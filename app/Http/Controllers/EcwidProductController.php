<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EcwidProductController extends Controller
{
    
    /**
     * Get all categories
     */
    public function search( Request $request ) {
        
        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->withQueryParameters([
                "categories" => $request->query('categories'),
            ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/products" );
    
            if ( $response->successful() ) {
    
                $productsResponse = $response->json();
    
                if ( $productsResponse && $productsResponse["total"] >= 1 ) {
                    // return $productsResponse;
                    return response($productsResponse, 200);
                }
    
                return [
                    "status" => 404,
                    "message" => "No products were found on Ecwid."
                ];
    
            }

            return [
                "status" => 500,
                "message" => "Unable to connect to the platform. Validate access tokens."
            ];

        }

        return [
            "status" => 400,
            "message" => $validEcwidConfig["message"]
        ];

    }

    /**
     * Get all categories
     */
    public function getOne( $productId ) {
        
        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/products/" . $productId );
    
            if ( $response->successful() ) {
    
                $productsResponse = $response->json();
    
                if ( $productsResponse ) {
                    // return $productsResponse;
                    return response($productsResponse, 200);
                }
    
                return [
                    "status" => 404,
                    "message" => "No products were found on Ecwid."
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
