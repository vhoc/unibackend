<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Models\CatalogOptions;

class MlaCatalogOptionsController extends Controller
{
    
    public function index( $userId ) {

        try {
            $catalog_options = CatalogOptions::where('user_id', $userId)->first();
            if ( $catalog_options ) {
                return response( $catalog_options, 200 );
            } else {
                return response( [
                    "status" => 404,
                    "message" => "No se encontraron las opciones de éste catalogo",
                ], 200 );
            }
        } catch (\Throwable $e) {
            return response([
                "status" => $e->getCode(),
                "message" => $e->getMessage(),
            ], $e->getCode());
        }

    }

}
