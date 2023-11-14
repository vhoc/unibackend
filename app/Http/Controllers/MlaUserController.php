<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MlaContactMethod;
use Illuminate\Auth\Events\Registered;

class MlaUserController extends Controller
{
    public function index() {

        $catalogs = User::where('email_verified_at', !null)->get();

        if ( $catalogs ) {
            return response($catalogs, 200);
        } else {
            return response([
                'status' => 404,
                'message' => "No existen catálogos"
            ]);
        }

    }

    public function register( Request $request ) {

        // Validate request data
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'whatsapp' => 'string|nullable',
            'facebook' => 'string|nullable',
            'contact_email' => 'email|nullable',
            'phone' => 'string|nullable'
            
        ]);

        $newUser = [
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt( $fields['password'] ),
            'type' => 'Otro'
        ];

        // Create local user
        $user = User::create($newUser);

        $user->hexId = dechex($user->id);
        $user->save();

        if ( $fields['whatsapp'] ) {
            $newWhatsapp = [
                "user_id" => $user->id,
                "type" => "whatsapp",
                "value" => $fields['whatsapp'],
            ];

            $fb = MlaContactMethod::create($newWhatsapp);
        }

        if ( $fields['facebook'] ) {
            $newFacebook = [
                "user_id" => $user->id,
                "type" => "facebook",
                "value" => $fields['facebook'],
            ];

            $fb = MlaContactMethod::create($newFacebook);
        }

        if ( $fields['phone'] ) {
            $newPhone = [
                "user_id" => $user->id,
                "type" => "phone",
                "value" => $fields['phone'],
            ];

            $ph = MlaContactMethod::create($newPhone);
        }

        if ( $fields['contact_email'] ) {
            $newContactEmail = [
                "user_id" => $user->id,
                "type" => "contact_email",
                "value" => $fields['contact_email'],
            ];

            $ce = MlaContactMethod::create($newContactEmail);
        }

        try {
            // Send verification email.
            event(new Registered($user));

            // Generate and send response with created local user.
            $response = [
                'user' => $user,
            ];

            // Return a sucess response.
            return response( $response, 201 );
        } catch (\Throwable $e) {
            return response([
                "status" => $e->code,
                "message" => $e->message,
            ]);
        }
    }

    public function show( $hexId ) {

        try {

            $user = User::where('hexId', $hexId)->first();

            if ( $user ) {
                return response([
                    "status" => 200,
                    "catalog" => [
                        "id" => $user->id,
                        "hexId" => $user->hexId,
                        "name" => $user->name,
                    ],
                ], 200);
            } else {
                return response([
                    "status" => 404,
                    "message" => "No se encontró el catálogo"
                ], 404);
            }

        } catch (\Throwable $e) {
            return response([
                "status" => $e->getCode(),
                "message" => $e->getMessage(),
            ], $e->getCode());
        }       

    }

    public function showById( $id ) {

        try {

            $user = User::where('id', $id)->first();

            if ( $user ) {
                return response([
                    "status" => 200,
                    "catalog" => [
                        "id" => $user->id,
                        "hexId" => $user->hexId,
                        "name" => $user->name,
                    ],
                ], 200);
            } else {
                return response([
                    "status" => 404,
                    "message" => "No se encontró el catálogo"
                ], 404);
            }

        } catch (\Throwable $e) {
            return response([
                "status" => $e->getCode(),
                "message" => $e->getMessage(),
            ], $e->getCode());
        }       

    }
}
