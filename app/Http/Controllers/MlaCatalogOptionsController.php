<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatalogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// use App\Models\MlaImage;

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

    public function updateImage( Request $request ) {

        $user = Auth::user();
        $userId = $user->id;

        try {
            
            // Get image from the request
            $originalFileName = $request->file('file')->getClientOriginalName();

            // If an image was uploaded check if the image already exists in the product's directory
            if ($originalFileName) {

                // Store the new uploaded image.
                Storage::disk('public')->makeDirectory('uploads/' . strval($userId) );
                $path = $request->file('file')->storeAs('public/uploads/' . strval($userId) . '/' . 'hero-image.jpg');                

                // Check if this product has an image according to the DB.
                $userOptions = CatalogOptions::where('user_id', $userId)->first();

                // If an image assossiated with a userID exists, update the URL in the database,
                if ( $userOptions ) {
                    // We have to delete the image first before in the DB, otherwise we lose the current filename.
                    if ( Storage::disk('public')->exists('uploads/' . strval($userId) . '/' . 'hero-image.jpg') ) {
                        Storage::disk('public')->delete('uploads/' . strval($userId) . '/' . 'hero-image.jpg');
                    }

                    $userOptions->heading_image_url = env("FRONTEND_URL") . '/uploads/' . strval($userId) . '/' . 'hero-image.jpg';
                    $userOptions->save();
                    
                // if not, create a new record.
                } else {
                    $newCatalogOption = CatalogOptions::create([
                        "user_id" => $userId,
                        "heading_image_url" => env("FRONTEND_URL") . '/uploads/' . strval($userId) . '/' . 'hero-image.jpg',
                    ]);
                    $newCatalogOption->save();
                }
            }

            return response([
                "status" => 201,
                "message" => "El archivo ha sido subido correctamente.",
                "image_url" => env("FRONTEND_URL") . '/uploads/' . strval($userId) . '/' . 'hero-image.jpg',// THIS WILL REQUIRE UPGRADE WHEN SUPPORTING MULTIPLE IMAGES
            ], 201);
        } catch (\Throwable $error) {
            return response([
                "status" => 500,
                "message" => $error->getMessage(),
            ]);
        }

    }

}
