<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\MlaImage;
use App\Models\MlaProduct;

class MlaImageController extends Controller
{
    
    public function store( Request $request ) {
        
        $user = Auth::user();
        $userId = $user->id;
        $productId = $request->header('product-id');
        // return response([
        //     "user" => $user,
        //     "userId" => $userId,
        //     "productId" => $productId,
        // ]);

        try {
            
            // Get image from the request
            $originalFileName = $request->file('file')->getClientOriginalName();

            // If an image was uploaded check if the image already exists in the product's directory
            if ($originalFileName) {
                // Delete all the images in the current catalog folder
                // THIS ONLY WORKS WHEN ONLY ONE IMAGE IS SUPPORTED.
                // PREMIUM WILL REQUIRE TO DELETE OR REPLACE SPECIFIC IMAGES
                // since those accounts will be able to upload several images per product.
                // $imagesInDir = Storage::allFiles('public/uploads/' . strval($userId) );
                // Storage::delete($imagesInDir);

                // Store the new uploaded image.
                Storage::disk('public')->makeDirectory('uploads/' . strval($userId) );
                $path = $request->file('file')->storeAs('public/uploads/' . strval($userId) . '/' . $originalFileName);

                // Check if this product has an image according to the DB.
                $currentImage = MlaImage::where('mla_product_id', $productId)->first();

                // If an image assossiated with a productId exists, update the URL in the database,
                if ( $currentImage ) {
                    // We have to delete the image first before in the DB, otherwise we lose the current filename.
                    if ( Storage::disk('public')->exists('uploads/' . strval($userId) . '/' . $currentImage->filename) ) {
                        Storage::disk('public')->delete('uploads/' . strval($userId) . '/' . $currentImage->filename);
                    }

                    $currentImage->url = env("APP_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName;
                    $currentImage->save();

                    
                // if not, create a new record.
                } else {
                    $newImage = MlaImage::create([
                        "mla_product_id" => $productId,
                        "url" => env("APP_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName,
                        "filename" => $originalFileName,
                    ]);
                    $newImage->save();
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

    // public function store( Request $request ) {
        
    //     $user = Auth::user();
    //     $userId = $user->id;

    //     try {
    //         // Store the image in the filesystem
    //         $originalFileName = $request->file('file')->getClientOriginalName();
    //         Storage::disk('public')->makeDirectory('uploads/' . strval($userId) );
    //         $path = $request->file('file')->storeAs('public/uploads/' . strval($userId) . '/' . $originalFileName);

    //         // Store the image URL in the database
    //         $newImage = MlaImage::create([
    //             "mla_product_id" => $request->header('product-id'),
    //             "url" => env("FRONTEND_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName,
    //         ]);

    //         $newImage->save();

    //         return response([
    //             "status" => 201,
    //             "message" => "El archivo ha sido subido correctamente.",
    //             "image_url" => env("FRONTEND_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName,// THIS WILL REQUIRE UPGRADE WHEN SUPPORTING MULTIPLE IMAGES
    //         ], 201);
    //     } catch (\Throwable $error) {
    //         return response([
    //             "status" => 500,
    //             "message" => $error->getMessage(),
    //         ]);
    //     }

    // }

    public function index( $productId ) {
        try {
            $images = MlaProduct::where('id', $productId)->first()->mla_images()->get();
            // $images = MlaProduct::where('id', $productId)->first();

            return response([
                "status" => 200,
                "images" => $images,
            ]);
        } catch (\Throwable $error) {
            return response([
                "status" => $error->getCode(),
                "message" => $error->getMessage(),
            ]);
        }
    }


}
