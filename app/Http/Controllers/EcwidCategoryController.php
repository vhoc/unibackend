<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EcwidCategoryController extends Controller
{
    
    /**
     * Get all categories
     */
    public function getAll() {
        
        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/categories" );
    
            if ( $response->successful() ) {
    
                $categoriesResponse = $response->json();
    
                if ( $categoriesResponse && $categoriesResponse["total"] >= 1 ) {
                    // return $categoriesResponse;
                    return response($categoriesResponse, 200);
                }
    
                return [
                    "status" => 404,
                    "message" => "No categories found on Ecwid."
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
     * Get one category
     */
    public function getOne( string $categoryId ) {
        
        $validEcwidConfig = validEcwidConfig();

        if ( $validEcwidConfig["status"] === true ) {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => ( env('ECWID_API_SECRET_TOKEN') ) ? env('ECWID_API_SECRET_TOKEN') : env('ECWID_API_PUBLIC_TOKEN')
            ])->get( env('ECWID_API_BASE_URL') . env('ECWID_STORE_ID') . "/categories/" . $categoryId );
    
            if ( $response->successful() ) {
    
                $categoriesResponse = $response->json();
    
                if ( $categoriesResponse ) {
                    // return $categoriesResponse;
                    return response($categoriesResponse, 200);
                }
    
                return [
                    "status" => 404,
                    "message" => "No categories found on Ecwid with that ID."
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

}
