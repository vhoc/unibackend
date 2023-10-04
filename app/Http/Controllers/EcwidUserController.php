<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EcwidUserController extends Controller
{
    
    // Get one user.
    public static function get( $email ) {

        if ( validEcwidConfig()["status"] === true ) {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->withQueryParameters([
                'email' => $email
            ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/customers" );
    
            if ( $response->successful() ) {
    
                $ecwidUser = $response->json();
    
                if ( $ecwidUser && $ecwidUser["total"] >= 1 ) {
                    return [
                        "status" => 200,
                        "user" => $ecwidUser["items"][0]
                    ];
                }
    
                return [
                    "status" => 404,
                    "message" => "User not found on Ecwid platform."
                ];
    
            }

        }

        return [
            "status" => 400,
            "message" => validEcwidConfig()["message"]
        ];

    }

    // Create user.
    public static function create( $email, $password, $customerGroupId, $billingPerson ) {

        if ( validEcwidConfig()["status"] === true ) {

            $response = Http::withHeaders([
                'method' => 'POST',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->post( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/customers", [
                'email' => $email,
                'password' => $password,
                'customerGroupId' => $customerGroupId,
                'billingPerson' => $billingPerson,
            ] );

            if ( $response->successful() ) {
                return $response->json();
            }

            return [
                "status" => 500,
                "message" => "Error creating the user on ECWID platform."
            ];

        }

    }
}
