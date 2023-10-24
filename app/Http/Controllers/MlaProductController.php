<?php

namespace App\Http\Controllers;

use App\Models\MlaProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
            'description' => 'string',
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
            $newProduct->images = [ env('FRONTEND_URL') . '/uploads/' . $userId . '/' . $newProduct->id . '-' . '1.jpg' ];
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
    public function show(MlaProduct $mlaProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MlaProduct $mlaProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MlaProduct $mlaProduct)
    {
        //
    }
}
