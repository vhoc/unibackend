<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MlaImageController extends Controller
{
    
    public function store( Request $request ) {
        
        $user = Auth::user();
        $userId = $user->id;       

        try {
            $originalFileName = $request->file('file')->getClientOriginalName();
            Storage::disk('public')->makeDirectory('uploads/' . strval($userId) );
            $path = $request->file('file')->storeAs('public/uploads/' . strval($userId) . '/' . $originalFileName);

            return response([
                "status" => 201,
                "message" => "El archivo ha sido subido correctamente.",
                "image_url" => env("FRONTEND_URL") . '/uploads/' . strval($userId) . '/' . $originalFileName,// THIS WILL REQUIRE UPGRADE WHEN SUPPORTING MULTIPLE IMAGES
            ], 201);
        } catch (\Throwable $error) {
            return response([
                "status" => 500,
                "message" => $error->getMessage(),
            ]);
        }

    }

}
