<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatalogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function update( Request $request ) {

        $user = Auth::user();
        $userId = $user->id;
        $userOptions = CatalogOptions::where('user_id', $userId)->first();

        $fields = $request->validate([
            'user_id' => 'required|alpha_num',
            'background_color_1' => 'string|nullable',
            'background_color_2' => 'string|nullable',
            'background_gradient_shape' => 'string|nullable',
            'custom_title' => 'string|nullable',
            'custom_subtitle' => 'string|nullable',
            'color_title' => 'string|nullable',
            'color_subtitle' => 'string|nullable',
        ]);

        // $product = [
        //     'title' => $fields['title'],
        //     'description' => $fields['description'],
        //     'price' => $fields['price'],
        // ];

        try {
            // UPDATE IN THE DATABASE 
            $userOptions->user_id = $fields['user_id'];
            $userOptions->background_color_1 = $fields['background_color_1'];
            $userOptions->background_color_2 = $fields['background_color_2'];
            $userOptions->background_gradient_shape = $fields['background_gradient_shape'];
            $userOptions->custom_title = $fields['custom_title'];
            $userOptions->custom_subtitle = $fields['custom_subtitle'];
            $userOptions->color_title = $fields['color_title'];
            $userOptions->color_subtitle = $fields['color_subtitle'];
            
            // Save all the changes
            $userOptions->save();

            return response( $userOptions, 200 );
        } catch ( \Throwable $e ) {
            return response([
                "status" => $e->getCode(),
                "message" => $e->getMessage(),
            ]);
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
                try {
                    $path = $request->file('file')->storeAs('public/uploads/' . strval($userId) . '/' . $originalFileName);

                } catch (\Throwable $e) {
                    return response([
                        "status" => $e->getCode(),
                        "message" => $e->getMessage(),
                    ]);
                }

                $userOptions = CatalogOptions::where('user_id', $userId)->first();
                // If an image assossiated with a userID exists, update the URL in the database,
                if ( $userOptions ) {
                    // Get current image filename
                    $currentImageFileName = Str::afterLast($userOptions->heading_image_url, '/');

                    // We have to delete the image first before in the DB, otherwise we lose the current filename.
                    if ( Storage::disk('public')->exists('uploads/' . strval($userId) . '/' . $currentImageFileName) ) {
                        Storage::disk('public')->delete('uploads/' . strval($userId) . '/' . $currentImageFileName);
                    }

                    $userOptions->heading_image_url = env("APP_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName;
                    $userOptions->save();
                    
                // if not, create a new record.
                } else {
                    $newCatalogOption = CatalogOptions::create([
                        "user_id" => $userId,
                        "heading_image_url" => env("APP_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName,
                    ]);
                    $newCatalogOption->save();
                }
            }

            return response([
                "status" => 201,
                "message" => "El archivo ha sido subido correctamente.",
                "image_url" => env("APP_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName,// THIS WILL REQUIRE UPGRADE WHEN SUPPORTING MULTIPLE IMAGES
            ], 201);
        } catch (\Throwable $error) {
            return response([
                "status" => 500,
                "message" => $error->getMessage(),
            ]);
        }

    }

}
