<?php

namespace App\Http\Controllers;

use App\Models\MlaProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MlaImage;
use Illuminate\Support\Facades\Storage;

class MlaProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( string $userId )
    {
        try {
            $products = User::where('id', $userId)->first()->mla_products()->get();

            if ( $products ) {
                return response($products);
            }

            return response([
                "status" => 404,
                "message" => "No se encontraron productos."
            ]);
        } catch (\Throwable $error) {
            return response([
                "status" => 404,
                "message" => "No se encontraron productos."
            ]);
        }
        

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $user = Auth::user();
        $userId = $user->id;
        
        $fields = $request->validate([
            'title' => 'required|string',
            'description' => 'string|nullable',
            'price' => 'required|numeric',
        ]);

        $product = [
            'user_id' => $userId,
            'hexId' => '0',
            'title' => $fields['title'],
            'description' => $fields['description'],
            'price' => $fields['price'],
            'images' => [""],
        ];

        try {
            $newProduct = MlaProduct::create($product);
            $newProduct->hexId = dechex($newProduct->id);
            // $newProduct->images = [ env('FRONTEND_URL') . '/uploads/' . $userId . '/' . $newProduct->id . '-' . '1.jpg' ];
            $newProduct->save();
            // Return a sucess response.
            return response( $newProduct, 201 );
        } catch ( \Throwable $error ) {
            return response($error);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show( $productHexId )
    {
        $product = MlaProduct::where( 'hexId', $productHexId )->first();

        if ( $product ) {
            return response( $product, 200 );
        } else {
            return response([
                "status" => 404,
                "message" => "El producto especificado no existe."
            ], 404); 
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        
        $fields = $request->validate([
            'id' => 'required',
            'title' => 'required|string',
            'description' => 'string|nullable',
            'price' => 'required|numeric',
        ]);

        $product = [
            'id' => $fields['id'],
            'title' => $fields['title'],
            'description' => $fields['description'],
            'price' => $fields['price'],
        ];

        try {
            // UPDATE IN THE DATABASE 
            $currentProduct = MlaProduct::where('id', $product['id'])->first();
            // return response($currentProduct, 203);
            $currentProduct->title = $product['title'];
            $currentProduct->description = $product['description'];
            $currentProduct->price = $product['price'];
            
            // Save all the changes
            $currentProduct->save();

            return response( $currentProduct, 201 );
        } catch ( \Throwable $error ) {
            return response($error);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productId)
    {
        $product = MlaProduct::find($productId);
        $user = Auth::user();
        $userId = $user->id;

        if ( $product ) {
            $product->delete();

            // Delete the image physically
            // Check if this product has an image according to the DB.
            $currentImage = MlaImage::where('mla_product_id', $productId)->first();

            // If an image assossiated with a productId exists, update the URL in the database,
            if ( $currentImage ) {
                // We have to delete the image first before in the DB, otherwise we lose the current filename.
                if ( Storage::disk('public')->exists('uploads/' . strval($userId) . '/' . $currentImage->filename) ) {
                    Storage::disk('public')->delete('uploads/' . strval($userId) . '/' . $currentImage->filename);
                }
                $currentImage->delete();
            }

            return response([
                "status" => 200,
                "message" => "El producto ha sido eliminado"
            ], 200);
        } else {
            return response([
                "status" => 404,
                "message" => "El producto que se intentó eliminar no existe"
            ], 404);
        }
    }
}
