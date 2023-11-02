<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EcwidOrderController extends Controller
{
    /** Calculate order */
    public function calculate( Request $request ) {

        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->post( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/order/calculate", $request );
    
            if ( $response->successful() ) {
    
                $orderResponse = $response->json();
    
                if ( $orderResponse ) {
                    return response($orderResponse, 200);
                }
    
                return [
                    "status" => 404,
                    "message" => "Unable to calculate the order."
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

    /** Create order on ECWID */
    public function create( Request $request ) {

        $payload = $request->all();

        // return json_encode($payload);

        $response = Http::withHeaders([
            'User-Agent' => 'insomnia/2023.5.8',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer secret_kypvdiibMGP31Qhtr52wFc3WF1s95c5Y',
            // 'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
        ])
        ->acceptJson()
        ->post( 'https://app.ecwid.com/api/v3/80090525/orders', $payload );
        
        return response($response);

    }
}
